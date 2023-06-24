<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\LaravelResourceController;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends LaravelResourceController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', [Permission::class, $this->authorizer]);

        return Inertia::render('Admin/Permissions/IndexPermission', [
            'permissions' => Permission::paginate(20),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->authorize('create', [Permission::class, $this->authorizer]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request could be removed safely?
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->authorize('create', [Permission::class, $this->authorizer]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        return $this->authorize('view', [Permission::class, $permission, $this->authorizer]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        return $this->authorize('update', [Permission::class, $permission, $this->authorizer]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request could be removed safely?
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        return $this->authorize('update', [Permission::class, $permission, $this->authorizer]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        return $this->authorize('delete', [Permission::class, $permission, $this->authorizer]);
    }
}
