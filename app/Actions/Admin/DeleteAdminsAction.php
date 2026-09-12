<?php

namespace App\Actions\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DeleteAdminsAction
{
    /**
     * @param  int[]  $ids
     */
    public function execute(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $users = User::whereIn('id', $ids)->get();

            $blocked = $users->firstWhere('used_before', true);

            if ($blocked) {
                throw new HttpException(403, 'You cannot delete an admin with previous activity on the system.');
            }

            $adminIds = $users->pluck('profile_id')->filter();
            Admin::whereIn('id', $adminIds)->delete();

            User::whereIn('id', $ids)->delete();
        });
    }
}