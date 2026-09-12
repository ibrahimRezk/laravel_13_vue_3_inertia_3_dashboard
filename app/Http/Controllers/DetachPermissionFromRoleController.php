<?php

namespace App\Http\Controllers;

use App\Actions\Role\RolePermissionAction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DetachPermissionFromRoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:edit role'),
        ];
    }

    public function __invoke(Request $request, RolePermissionAction $action)
    {
        $validated = $request->validate([
            'roleId'       => ['required', 'integer', 'exists:roles,id'],
            'permissionId' => ['required', 'integer', 'exists:permissions,id'],
            'type'         => ['required', 'integer', 'in:1,2'],
            'userId'       => ['required_if:type,2', 'integer', 'exists:users,id'],
        ]);

        try {
            $action->detach(
                $validated['roleId'],
                $validated['permissionId'],
                $validated['type'],
                $validated['userId'] ?? null,
            );

            return ['message' => 'permissions updated successfully', 'result' => 'success'];
        } catch (\Throwable $th) {
            report($th);

            return ['message' => 'sorry something went wrong', 'result' => 'error'];
        }
    }
}