<?php

namespace App\Actions\Role;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RolePermissionAction
{
    public function attach(int $roleId, int $permissionId, int $type, ?int $userId = null): void
    {
        $this->apply($roleId, $permissionId, $type, $userId, attach: true);
    }

    public function detach(int $roleId, int $permissionId, int $type, ?int $userId = null): void
    {
        $this->apply($roleId, $permissionId, $type, $userId, attach: false);
    }

    private function apply(int $roleId, int $permissionId, int $type, ?int $userId, bool $attach): void
    {
        $role = Role::findOrFail($roleId);

        if ($role->name === 'Super Admin') {
            throw new HttpException(403, 'general.can_not_modify_super_admin_permissions');
        }

        $permission = Permission::findById($permissionId);
        $target = match ($type) {
            1 => $role,
            2 => User::without(['media', 'profile'])->findOrFail($userId),
            default => throw new HttpException(422, 'general.invalid_permission_type'),
        };

        $attach ? $target->givePermissionTo($permission) : $target->revokePermissionTo($permission);
    }
}