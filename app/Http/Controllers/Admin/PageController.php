<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\LaravelResourceController;
use App\Models\Category;
use App\Models\Content;
use App\Models\ContentPart;
use App\Models\Padalinys;
use App\Models\Page;
use App\Services\ModelIndexer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends LaravelResourceController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', [Page::class, $this->authorizer]);

        $indexer = new ModelIndexer(new Page(), request(), $this->authorizer);

        $pages = $indexer
            ->setEloquentQuery()
            ->filterAllColumns()
            ->sortAllColumns(['created_at' => 'desc'])
            ->builder->paginate(20);

        return Inertia::render('Admin/Content/IndexPages', [
            'pages' => $pages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', [Page::class, $this->authorizer]);

        return Inertia::render('Admin/Content/CreatePage',
            [
                'categories' => Category::all(['id', 'name']),
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', [Page::class, $this->authorizer]);

        $request->validate([
            'title' => 'required|string|max:255',
            'content.parts' => 'required',
            'lang' => 'required|string',
            'permalink' => 'required|string|max:255|unique:pages',
        ]);

        $padalinys_id = null;

        // check if super admin, else set padalinys_id
        if (request()->user()->hasRole(config('permission.super_admin_role_name'))) {
            $padalinys_id = Padalinys::where('type', 'pagrindinis')->first()->id;
        } else {
            $padalinys_id = $this->authorizer->permissableDuties->first()->padaliniai->first()->id;
        }

        $content = new Content();

        $content->save();

        $content->parts()->createMany($request->content['parts']);

        Page::query()->create([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'content_id' => $content->id,
            'permalink' => $request->permalink,
            'lang' => $request->lang,
            'other_lang_id' => $request->other_lang_id,
            'padalinys_id' => $padalinys_id,
        ]);

        return redirect()->route('pages.index')->with('success', 'Puslapis sėkmingai sukurtas!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        $this->authorize('update', [Page::class, $page, $this->authorizer]);

        $other_lang_pages = Page::with('padalinys:id,shortname')->when(! request()->user()->hasRole(config('permission.super_admin_role_name')), function ($query) use ($page) {
            $query->where('padalinys_id', $page->padalinys_id);
        })->where('lang', '!=', $page->lang)->select('id', 'title', 'padalinys_id')->get();

        return Inertia::render('Admin/Content/EditPage', [
            'page' => [
                ...$page->only('id', 'title', 'content', 'permalink', 'text', 'lang', 'category_id', 'padalinys_id', 'is_active', 'aside'),
                'other_lang_id' => $page->getOtherLanguage()?->only('id')['id'],
            ],
            'otherLangPages' => $other_lang_pages,
            'categories' => Category::all(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Page $page)
    {
        $this->authorize('update', [Page::class, $page, $this->authorizer]);

        $other_lang_page = Page::find($page->other_lang_id);

        $page->update($request->only('title', 'lang', 'other_lang_id', 'category_id'));

        $content = Content::query()->find($page->content->id);

        // Collect and remove values with no ids
        $existingParts = collect($request->content['parts'])->filter(function ($part) {
            return isset($part['id']);
        });

        // Remove non-existing parts
        $content->parts()->whereNotIn('id', $existingParts->pluck('id'))->delete();

        foreach ($request->content['parts'] as $key => $part) {

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

        // update other lang id page
        if ($request->other_lang_id) {
            // overwrite other lang id page
            $other_lang_page = Page::find($request->other_lang_id);
            $other_lang_page->other_lang_id = $page->id;
            $other_lang_page->save();
        } elseif (is_null($request->other_lang_id) && ! is_null($other_lang_page)) {
            $other_lang_page->other_lang_id = null;
            $other_lang_page->save();
        }

        return back()->with('success', 'Puslapis atnaujintas!')->with('data', $page->load('content'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        $this->authorize('delete', [Page::class, $page, $this->authorizer]);

        $page->delete();

        return redirect()->route('pages.index')->with('info', 'Puslapis ištrintas');
    }

    public function restore(Page $page, Request $request)
    {
        $this->authorize('restore', [Page::class, $page, $this->authorizer]);

        $page->restore();

        return back()->with('success', 'Puslapis sėkmingai atkurtas!');
    }
}
