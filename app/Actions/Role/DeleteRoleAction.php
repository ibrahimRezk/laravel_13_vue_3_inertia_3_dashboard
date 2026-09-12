<?php

namespace App\Actions\Role;

use App\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DeleteRoleAction
{
    public function execute(Role $role): void
    {
        if ($role->id === 1) {
            throw new HttpException(403, 'general.can_not_delete_super_admin_role');
        }

        if ($role->used_before) {
            throw new HttpException(403, 'general.item_has_previous_activity_or_no_permission');
        }

        $role->delete();
    }
}