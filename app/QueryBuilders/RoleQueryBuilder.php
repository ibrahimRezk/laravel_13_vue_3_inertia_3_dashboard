<?php

namespace App\QueryBuilders;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RoleQueryBuilder
{
    public static function forRequest(Request $request): Builder
    {
        return Role::query()
            ->select(['slug', 'id', 'used_before', 'created_at'])
            ->when($request->filled('name'), fn (Builder $q) =>
                $q->whereAny(['slug->ar', 'slug->en'], 'like', "%{$request->name}%")
            )
            ->oldest('id');
    }
}