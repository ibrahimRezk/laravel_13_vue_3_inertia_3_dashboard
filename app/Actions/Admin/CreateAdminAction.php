<?php

namespace App\Actions\Admin;

use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateAdminAction
{
    public function execute(array $validated, int $roleId): User
    {
        return DB::transaction(function () use ($validated, $roleId) {
            $user = User::create([
                'email'    => $validated['email'],
                'active'   => (bool) $validated['active'],
                'name'     => [
                    'ar' => $validated['name']['ar'],
                    'en' => $validated['name']['en'],
                ],
                'password' => Hash::make($validated['password']),
            ]);

            $admin = Admin::create([
                'added_by' => auth()->id(),
                'phone'    => $validated['phone'],
            ]);

            $admin->user()->save($user);

            $role = Role::findOrFail($roleId);
            $role->update(['used_before' => true]);
            $user->assignRole($role);

            return $user;
        });
    }
}