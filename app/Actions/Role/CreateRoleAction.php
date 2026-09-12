<?php

namespace App\Actions\Role;

use App\Models\Role;

class CreateRoleAction
{
    public function execute(array $validated): Role
    {
        return Role::create([
            'name'       => $validated['name'],
            'slug'       => [
                'ar' => $validated['slug']['ar'],
                'en' => $validated['slug']['en'],
            ],
            'guard_name' => 'web',
        ]);
    }
}