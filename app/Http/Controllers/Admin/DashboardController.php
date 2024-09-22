<?php

namespace App\Http\Controllers\Admin;

use App\Actions\GetTenantsForUpserts;
use App\Http\Controllers\Controller as Controller;
use App\Models\Institution;
use App\Models\Page;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ModelAuthorizer as Authorizer;
use App\Services\RelationshipService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(public Authorizer $authorizer) {}

    public function dashboard()
    {
        // load user duty institutions
        // TODO: need to make it current duty institutions.
        $user = User::with('current_duties.institution.tenant', 'current_duties.institution.duties.current_users:users.id,users.name,profile_photo_path,phone')->with(['doings' => function (Builder $query) {
            $query->with('comments', 'tasks')->where('deleted_at', null)->orderBy('date', 'desc');
        }])->with('reservations')->find(auth()->user()->id);

        $institutions = $user->current_duties->pluck('institution')->flatten()->unique()->values();

        // convert to eloquent collection
        $institutions = new EloquentCollection($institutions);

        // NOTE: The filter() is needed, because some duties may have no institutions and they are null here
        $institutions = $institutions->filter()->load(['meetings' => function ($query) {
            // Load institutions with meetings where start_time is in the future.
            $query->where('start_time', '>', now())->with('comments', 'tasks', 'files');
        }])->values();

        return Inertia::render('Admin/ShowDashboard', [
            'currentUser' => [
                ...$user->toArray(),
                'institutions' => $institutions->map(function (Institution $institution) {
                    return [
                        ...$institution->toArray(),
                        'users' => $institution->duties->pluck('current_users')->flatten()->unique()->values(),
                    ];
                }),
                'doings' => $user->doings->sortBy('date')->values(),
            ],
        ]);
    }

    public function atstovavimas()
    {
        $selectedTenant = request()->input('tenant_id');

        $user = User::query()->where('id', Auth::id())->with('current_duties.institution.meetings.institutions:id,name')->first();

        // Leave only tenants that are not 'pkp'
        $tenants = collect(GetTenantsForUpserts::execute('pages.update.padalinys', $this->authorizer))->filter(function ($tenant) {
            return $tenant['type'] !== 'pkp';
        })->values();

        // Check if selected tenant is in the list of tenants
        if ($selectedTenant) {
            $selectedTenant = $tenants->firstWhere('id', $selectedTenant);
        } else {
            // Check if there's tenant with type 'pagrindinis'
            $selectedTenant = $tenants->firstWhere('type', 'pagrindinis');
        }

        // If not, select first tenant
        if (! $selectedTenant) {
            $selectedTenant = $tenants->first();
        }

        if (! $selectedTenant) {
            $providedTenant = null;
        } else {
            $providedTenant = Tenant::query()->where('id', $selectedTenant['id'])->with('institutions:id,name,tenant_id', 'institutions.meetings:id,title,start_time', 'institutions.duties.current_users:id,name', 'institutions.duties.types:id,title,slug')->first();
        }

        return Inertia::render('Admin/Dashboard/ShowAtstovavimas', [
            'user' => $user,
            'tenants' => $tenants,
            'providedTenant' => $providedTenant,
        ]);
    }

    public function svetaine()
    {
        $this->authorize('viewAny', Page::class);

        $selectedTenant = request()->input('tenant_id');

        // Leave only tenants that are not 'pkp'
        $tenants = collect(GetTenantsForUpserts::execute('institutions.update.padalinys', $this->authorizer))->filter(function ($tenant) {
            return $tenant['type'] !== 'pkp';
        })->values();

        // Check if selected tenant is in the list of tenants
        if ($selectedTenant) {
            $selectedTenant = $tenants->firstWhere('id', $selectedTenant);
        } else {
            // Check if there's tenant with type 'pagrindinis'
            $selectedTenant = $tenants->firstWhere('type', 'pagrindinis');
        }

        // If not, select first tenant
        if (! $selectedTenant) {
            $selectedTenant = $tenants->first();
        }

        if (! $selectedTenant) {
            $providedTenant = null;
        } else {
            $providedTenant = Tenant::query()->where('id', $selectedTenant['id'])->with('pages', 'news', 'mainPages')->with(['calendar' => function ($query) {
                // get only future and pasts event 12 months ago
                $query->where('date', '>=', now()->subMonths(12))->orderBy('date', 'asc');
            }])->first();
        }

        return Inertia::render('Admin/Dashboard/ShowSvetaine', [
            'tenants' => $tenants,
            'providedTenant' => $providedTenant,
        ]);
    }

    public function userSettings()
    {
        $user = User::find(Auth::id());

        $user->load('roles:id,name',
            'duties:id,name,institution_id',
            'duties.roles:id,name', 'duties.roles.permissions:id,name',
            'duties.institution:id,tenant_id',
            'duties.institution.tenant:id,shortname');

        return Inertia::render('Admin/ShowUserSettings', [
            'user' => $user->toFullArray(),
        ]);
    }

    public function updateUserSettings(Request $request)
    {
        $user = User::find(Auth::id());

        $user->update($request->all());

        return redirect()->back()->with('success', 'Nustatymai išsaugoti.');
    }

    public function userTasks()
    {
        $user = User::find(auth()->user()->id);

        $tasks = $user->tasks->load('taskable', 'users');

        return Inertia::render('Admin/ShowTasks', [
            'tasks' => $tasks,
            'taskableInstitutions' => Inertia::lazy(fn () => Institution::select('id', 'name')->withWhereHas('users:users.id,users.name,profile_photo_path,phone')->get()),
        ]);
    }

    public function institutionGraph()
    {
        // return institutions with user count
        $institutions = Institution::withCount('users')->get();

        return Inertia::render('Admin/ShowInstitutionGraph', [
            'institutions' => $institutions,
            'institutionRelationships' => RelationshipService::getAllRelatedInstitutions(),
        ]);
    }

    public function sendFeedback(Request $request)
    {
        $request->validate([
            'feedback' => 'required|string',
            'anonymous' => 'boolean',
        ]);

        // just send simple email to it@vusa.lt with feedback, conditional user name and with in a queue
        Mail::to('it@vusa.lt')->queue(new \App\Mail\FeedbackMail($request->input('feedback'), $request->input('anonymous') ? null : auth()->user()));

        return redirect()->back()->with('success', 'Ačiū už atsiliepimą!');
    }

    protected function getInstitutionForWorkspace(Request $request)
    {
        $institution = null;

        // check if institution has id
        if (! is_null($request->input('institution_id'))) {
            $institution = Institution::with('meetings.comments', 'meetings.tasks', 'meetings.files', 'users')->find($request->input('institution_id'));
        }

        return $institution;
    }
}
