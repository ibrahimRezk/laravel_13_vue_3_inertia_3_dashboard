<?php

namespace App\QueryBuilders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminUserQueryBuilder
{
    public static function forRequest(Request $request): Builder
    {
        return User::query()
            ->select(['id', 'name', 'active', 'email', 'used_before', 'profile_type', 'profile_id', 'created_at'])
            ->with(['roles:roles.id,roles.name', 'media'])
            ->when($request->filled('name'), fn (Builder $q) =>
                $q->whereAny(['name->ar', 'name->en'], 'like', "%{$request->name}%")
            )
            ->when($request->active !== null, fn (Builder $q) =>
                $request->boolean('active') ? $q->active() : $q->inActive()
            )
            ->where('id', '<>', auth()->id())
            ->latest('id');
    }
}