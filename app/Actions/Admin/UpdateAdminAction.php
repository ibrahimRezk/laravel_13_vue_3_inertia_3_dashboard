<?php

namespace App\Actions\Admin;

use App\Models\Admin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateAdminAction
{
    public function execute(User $user, array $validated, int $roleId): User
    {
        return DB::transaction(function () use ($user, $validated, $roleId) {
            $data = [
                'email'  => $validated['email'],
                'active' => (bool) $validated['active'],
                'name'   => [
                    'ar' => $validated['name']['ar'],
                    'en' => $validated['name']['en'],
                ],
            ];

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            if ($admin = Admin::find($user->profile_id)) {
                $admin->update([
                    'phone'      => $validated['phone'],
                    'updated_by' => auth()->id(),
                ]);
            }

            $role = Role::findOrFail($roleId);
            $role->update(['used_before' => true]);
            $user->syncRoles($role);

            return $user;
        });
    }
}