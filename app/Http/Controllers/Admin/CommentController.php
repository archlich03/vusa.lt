<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ModelEnum;
use App\Http\Controllers\LaravelResourceController;
use App\Models\Comment;
use App\Models\Traits\MakesDecisions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Enum\Laravel\Rules\EnumRule;

class CommentController extends LaravelResourceController
{
    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->authorize('create', [Comment::class, $this->authorizer]);

        $validated = $request->validate([
            'commentable_type' => [new EnumRule(ModelEnum::class), 'required'],
            'commentable_id' => 'required',
            'comment' => 'required|string',
            'decision' => 'nullable|string',
            'route' => 'nullable|string',
        ]);

        // convert to camelCase
        $formatted = Str::ucfirst(Str::camel($validated['commentable_type']));

        if ($formatted === 'ReservationResource') {
            $modelClass = 'App\\Models\\Pivots\\ReservationResource';
        } else {
            $modelClass = 'App\\Models\\'.$formatted;
        }

        $model = $modelClass::find($request->commentable_id);

        if (isset($validated['decision']) && class_uses($model, MakesDecisions::class)) {
            $model->decision($validated['decision'], $this->authorizer);
        }

        $model->comment($request->comment, $request->decision);

        return back()->with('success', 'Komentaras pridėtas.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', [Comment::class, $comment, $this->authorizer]);

        // delete comment
        $comment->delete();
    }
}
