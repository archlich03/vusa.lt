<?php

namespace App\Services;

use App\Models\Model;
use App\Models\User;
use App\Services\ModelAuthorizer as Authorizer;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Scout\Builder;

class ModelIndexer
{
    public Builder $builder;

    private Model $indexable;

    private ?string $search;

    private ?array $sorters;

    public ?array $filters;

    private array $callbacksArray = [];

    private Authorizer $authorizer;

    private string $padalinysRelationString;

    public function __construct($indexable, $request, $authorizer)
    {
        $this->indexable = $indexable;
        $this->search = $request->input('text');
        $this->sorters = json_decode(base64_decode($request->input('sorters')), true);
        $this->filters = json_decode(base64_decode($request->input('filters')), true);

        $this->authorizer = $authorizer;

        $this->padalinysRelationString = $indexable->whichUnitRelation();

        $this->search();
        $this->setEloquentQuery();
        $this->takeCareOfRelationFilters();
    }

    public function search()
    {
        $this->builder = $this->indexable::search($this->search);

        return $this;
    }

    // This is needed because Laravel\Scout\Builder can support only one query
    public function setEloquentQuery($callbacks = [], $authorize = true)
    {

        $eloquentQueryClosure = function (EloquentBuilder $query) use ($callbacks, $authorize) {

            // add $callbacks to $this->callbacksArray
            $this->callbacksArray = array_merge($this->callbacksArray, $callbacks);

            $query->with($this->padalinysRelationString);

            if ($authorize) {
                // add authorizer closure to callbacks to the first element of the callbacks
                array_unshift($this->callbacksArray, $this->authorizerClosure());
            }

            foreach ($this->callbacksArray as $callback) {
                $callback($query);
            }
        };

        $this->builder->query($eloquentQueryClosure);

        return $this;
    }

    public function takeCareOfRelationFilters()
    {
        // check if filters keys have dots, get them and remove from filters

        if (! $this->filters) {
            $this->filters = [];
        }

        $relationFilters = array_filter($this->filters, fn ($key) => Str::contains($key, '.'), ARRAY_FILTER_USE_KEY);

        $this->filters = array_diff_key($this->filters, $relationFilters);

        // foreach relation filters, create a callback that is added to callbacksArray
        foreach ($relationFilters as $relationFilterKey => $relationFilterValue) {
            $relationFilterKeyArray = explode('.', $relationFilterKey);

            $relationFilterCallback = function (EloquentBuilder $query) use ($relationFilterKeyArray, $relationFilterValue) {
                $query->when($relationFilterValue !== [],
                fn (EloquentBuilder $query) => $query->whereHas($relationFilterKeyArray[0],
                fn (EloquentBuilder $query) => $query->whereIn($relationFilterKeyArray[1], $relationFilterValue)));
            };

            array_push($this->callbacksArray, $relationFilterCallback);
        }

        return $this;
    }

    private function authorizerClosure()
    {
        $user = User::query()->find((Auth::id()));

        return fn (EloquentBuilder $query) => $query->when(
            ! $this->authorizer->isAllScope && ! $user->hasRole(config('permission.super_admin_role_name')),
            fn (EloquentBuilder $query) => $query->whereHas(
                $this->padalinysRelationString, fn (EloquentBuilder $query) => $query->whereIn(
                    // Optional, because this is how relationship is queried in query builder
                    optional($this->padalinysRelationString === 'padaliniai', fn () => 'padaliniai.id'), $this->authorizer->getPadaliniai()->pluck('id')->toArray())
            )
        );
    }

    public function filterAllColumns()
    {
        if (! $this->filters) {
            return $this;
        }

        foreach ($this->filters as $name => $value) {
            $this->builder->when($value !== [], function (Builder $query) use ($name, $value) {
                $query->whereIn($name, $value);
            });
        }

        return $this;
    }

    public function sortAllColumns()
    {
        if (! $this->sorters) {
            return $this;
        }

        foreach ($this->sorters as $name => $value) {
            $this->builder->when($value, function (Builder $query) use ($name, $value) {
                $query->orderBy($name, $value === 'descend' ? 'desc' : 'asc');
            });
        }

        return $this;
    }

    // check argument to be class of model
    // public function execute(string $modelClass, ?string $search, string $searchable, Authorizer $authorizer, ?bool $hasManyPadalinys = true): Builder
    // {
    //     if (! class_exists($modelClass)) {
    //         // return exception that the class doesn't exist
    //         new Exception('Class '.$modelClass.' does not exist');
    //     }

    //     $user = User::find((Auth::id()));

    //     $padalinysRelationString = $hasManyPadalinys ? 'padaliniai' : 'padalinys';

    //     // first need to check if has permission to view all models

    //     // TODO: get only needed data for index
    //     if ($authorizer->isAllScope || $user->hasRole(config('permission.super_admin_role_name'))) {
    //         return $modelClass::with($padalinysRelationString)->where($searchable, 'like', "%{$search}%");
    //     }

    //     $modelsBuilder = $modelClass::whereHas($padalinysRelationString, function (Builder $query) use ($authorizer, $hasManyPadalinys) {
    //         $query->whereIn(optional($hasManyPadalinys, fn () => 'padaliniai.').'id', $authorizer->getPadaliniai()->pluck('id'));
    //     })->where($searchable, 'like', "%{$search}%");

    //     return $modelsBuilder;
    // }

    // // TODO: implement this method in all models (and make ModelIndexer non static)
    // public static function filterByAuthorized(\Laravel\Scout\Builder $builder, Authorizer $authorizer, ?bool $hasManyPadalinys = true)
    // {
    //     $user = User::query()->find((Auth::id()));

    //     $padalinysRelationString = $hasManyPadalinys ? 'padaliniai' : 'padalinys';

    //     // first need to check if has permission to view all models

    //     // TODO: get only needed data for index
    //     if ($authorizer->isAllScope || $user->hasRole(config('permission.super_admin_role_name'))) {
    //         return $builder->query(fn (Builder $query) => $query->with($padalinysRelationString));
    //     }

    //     return $builder->query(fn (Builder $query) => $query->whereHas($padalinysRelationString, function (Builder $query) use ($authorizer, $hasManyPadalinys) {
    //         $query->whereIn(optional($hasManyPadalinys, fn () => 'padaliniai.').'id', $authorizer->getPadaliniai()->pluck('id'));
    //     }));
    // }
}
