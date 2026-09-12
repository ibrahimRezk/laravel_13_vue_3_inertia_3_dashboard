<?php

namespace App\Actions\Role;

use App\Models\Role;

class UpdateRoleAction
{
    public function execute(Role $role, array $validated): Role
    {
        $role->update([
            'name' => $validated['name'],
            'slug' => [
                'ar' => $validated['slug']['ar'],
                'en' => $validated['slug']['en'],
            ],
        ]);

        return $role;
    }
}