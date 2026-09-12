<?php

namespace App\QueryBuilders;

use App\Models\Nationality;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class NationalityQueryBuilder
{
    public static function forRequest(Request $request): Builder
    {
        return Nationality::query()
            ->select(['id', 'name', 'used_before', 'added_by', 'updated_by', 'active', 'date', 'created_at', 'updated_at'])
            ->with(['added_by_user:id,name', 'updated_by_user:id,name'])
            ->when($request->filled('name'), fn (Builder $q) =>
                $q->whereAny(['name->ar', 'name->en'], 'like', "%{$request->name}%")
            )
            ->when($request->active !== null, fn (Builder $q) =>
                $request->boolean('active') ? $q->active() : $q->inActive()
            )
            ->latest('id');
    }
}