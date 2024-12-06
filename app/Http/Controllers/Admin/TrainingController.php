<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrainingRequest;
use App\Http\Requests\UpdateTrainingRequest;
use App\Models\Duty;
use App\Models\Institution;
use App\Models\Membership;
use App\Models\Training;
use App\Models\Type;
use App\Models\User;
use App\Services\ModelAuthorizer as Authorizer;
use App\Services\ModelIndexer;
use Inertia\Inertia;

class TrainingController extends Controller
{
    public function __construct(public Authorizer $authorizer) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Training::class);

        $indexer = new ModelIndexer(new Training);

        $trainings = $indexer
            ->setEloquentQuery()
            ->filterAllColumns()
            ->sortAllColumns()
            ->builder->paginate(15);

        return Inertia::render('Admin/People/IndexTraining', [
            'trainings' => $trainings,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Training::class);

        return Inertia::render('Admin/People/CreateTraining');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrainingRequest $request)
    {
        $training = new Training($request->validated());

        $training->organizer_id = auth()->id();

        $training->save();

        return redirect()->route('trainings.index')->with('success', 'Mokymų šablonas sukurtas');
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Training $training)
    {
        $training->load('form', 'tenant', 'organizer', 'trainingables', 'tasks');

        return Inertia::render('Admin/People/EditTraining', [

            'training' => [
                ...$training->toFullArray(),
                'form' => [
                    ...($training->form ? $training->form->toFullArray() : []),
                    'form_fields' => $training->form?->formFields()->orderBy('order')->get()->map->toFullArray(),
                ],
                'tasks' => $training->tasks->map->toFullArray(),
            ],
            'trainingableTypes' => [
                User::class => ['type' => User::class, 'name' => 'Narys', 'values' => User::query()->get(['id', 'name'])],
                Duty::class => ['type' => Duty::class, 'name' => 'Pareiga', 'values' => Duty::query()->get(['id', 'name'])],
                Institution::class => ['type' => Institution::class, 'name' => 'Institucija', 'values' => Institution::query()->get(['id', 'name'])],
                Membership::class => ['type' => Membership::class, 'name' => 'Narystė', 'values' => Membership::query()->get(['id', 'name'])],
                // TODO: Implement later (can't because id isn't ulid)
                /*Type::class => ['type' => Type::class, 'name' => 'Tipas', 'values' => Type::query()->where('model_type', 'App\Models\Duty')->orWhere('model_type', 'App\Models\Institution')->get(['id', 'title'])],*/
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainingRequest $request, Training $training)
    {
        $training->update($request->except('trainingables', 'tasks'));

        $training->trainingables()->delete();

        $training->trainingables()->createMany($request->trainingables);

        $training->tasks()->delete();

        $training->tasks()->createMany($request->tasks);

        return back()->with('success', 'Mokymų šablonas atnaujintas');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training)
    {
        $training->delete();

        return redirect()->route('trainings.index')->with('success', 'Mokymų šablonas ištrintas');
    }
}
