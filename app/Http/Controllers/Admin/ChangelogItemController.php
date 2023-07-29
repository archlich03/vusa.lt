<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\LaravelResourceController;
use App\Http\Requests\StoreChangelogItemRequest;
use App\Models\ChangelogItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Response as InertiaResponse;

class ChangelogItemController extends LaravelResourceController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): InertiaResponse
    {
        // return all
        return inertia('Admin/ModelMeta/IndexChangelogItem', [
            'changelogItems' => ChangelogItem::paginate(20),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): InertiaResponse
    {
        return inertia('Admin/ModelMeta/CreateChangelogItem');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChangelogItemRequest $request): RedirectResponse
    {
        ChangelogItem::create($request->only([
            'title',
            'description',
            'date',
        ]));

        return back()->with('success', 'Pasikeitimas sukurtas.');
    }

    public function approveForUser(): RedirectResponse
    {
        $user = User::find(auth()->id()); //->makeVisible('last_changelog_check');

        // get newest changelog item
        $user->last_changelog_check = ChangelogItem::query()->orderBy('date', 'desc')->first()->date;
        $user->save();

        return back()->with('success');
    }
}
