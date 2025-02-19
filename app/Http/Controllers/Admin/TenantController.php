<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Content;
use App\Models\ContentPart;
use App\Models\Institution;
use App\Models\Tenant;
use App\Services\ModelAuthorizer as Authorizer;
use Inertia\Inertia;

class TenantController extends Controller
{
    public function __construct(public Authorizer $authorizer) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Tenant::class);

        $tenants = Tenant::query()->paginate(15);

        // also check if empty array
        return Inertia::render('Admin/People/IndexTenant', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Tenant::class);

        return Inertia::render('Admin/People/CreateTenant', [
            'assignableInstitutions' => Institution::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTenantRequest $request)
    {
        $tenant = new Tenant;

        $tenant->fill($request->validated());

        $tenant->save();

        return redirect()->route('tenants.index')->with('success', 'Tenant created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        return Inertia::render('Admin/People/EditTenant', [
            'tenant' => $tenant,
            'assignableInstitutions' => Institution::all(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $tenant->fill($request->validated());

        $tenant->save();

        return redirect()->route('tenants.index')->with('success', 'Tenant updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        $this->authorize('delete', $tenant);

        $tenant->delete();

        return redirect()->route('tenants.index')->with('success', 'Tenant deleted.');
    }

    public function editMainPage(Tenant $tenant)
    {
        $this->authorize('updateMainPage', $tenant);

        $tenant->load('content.parts');

        if ($tenant->content === null) {
            $content = new Content;
            $content->save();
            $tenant->content()->associate($content)->save();
        }

        return Inertia::render('Admin/Content/EditHomePage', [
            'tenant' => $tenant->load('content.parts'),
        ]);
    }

    public function updateMainPage(UpdateContentRequest $request, Tenant $tenant)
    {
        $validated = $request->validated();

        $content = Content::query()->find($validated['id']);

        // Collect and remove values with no ids
        $existingParts = collect($validated['parts'])->filter(function ($part) {
            return isset($part['id']);
        });

        // Remove non-existing parts
        $content->parts()->whereNotIn('id', $existingParts->pluck('id'))->delete();

        foreach ($validated['parts'] as $key => $part) {

            // Continue if part is null
            if (is_null($part)) {
                continue;
            }

            $id = $part['id'] ?? null;

            $model = ContentPart::query()->findOrNew($id);

            $model->content_id = $content->id;
            $model->type = $part['type'];
            $model->json_content = $part['json_content'];
            $model->options = $part['options'] ?? null;
            $model->order = $key;

            $model->save();
        }

        return redirect()->back()->with('success', 'Tenant updated.');
    }
}
