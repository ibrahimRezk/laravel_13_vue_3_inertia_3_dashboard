<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controllers\Middleware;

class AttachPermissionToRoleController extends Controller implements HasMiddleware
{
    // public function __construct()
    // {
    //     $this->middleware('can:edit role');
    // }

            public static function middleware(): array
    {
        return [
            new Middleware('can:edit role'),
        ];
    }


    public function __invoke(Request $request)
    {
        try {
            DB::beginTransaction();
            $role = Role::find($request->roleId);
            if($role->name == 'Super Admin')
                {
                    abort(403 , 'error');
                    }
                    // return $request;
                    $permission = Permission::findById($request->permissionId);
                    
                    
            if ($permission != null)
                if ($request->type == 1) // normal permission to be assigned to a role
                {
                    $permission->assignRole($request->roleId);
                } 
                elseif ($request->type == 2)    // special permission to be assigned to a user model
                {
                    $user = User::without(['media', 'profile'])->find($request->userId);
                    $user->givePermissionTo($permission->name);
                }
            DB::commit();

            return ['message' => 'permissions updated successfully', 'result' => 'success'];

            // return redirect()->back()->with('success', 'permissions updated successfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return ['message' => 'sorry something went wrong', 'result' => 'error'];
        }
    }
}
