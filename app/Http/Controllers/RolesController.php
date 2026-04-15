<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\PagePermission;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RolesRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
// use Diglactic\Breadcrumbs\Breadcrumbs;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\PagePermissionResource;

class RolesController extends Controller implements HasMiddleware
{
    private string $routeResourceName = 'roles';

    // public function __construct()
    // {
    //     $this->middleware('can:view roles')->only('index');
    //     $this->middleware('can:create role')->only(['create', 'store']);
    //     $this->middleware('can:edit role')->only(['edit', 'update']);
    //     $this->middleware('can:delete role')->only('destroy');
    // }


        public static function middleware(): array
    {
        return [
            // Apply 'can' middleware to specific methods
            new Middleware('can:view roles', only: ['index']),
            new Middleware('can:create role', only: ['create' , 'store']),
            new Middleware('can:edit role', only: ['edit' , 'update']),
            new Middleware('can:delete role', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $roles = Role::query()
            ->select([
                'slug',
                'id',
                'used_before',
                'created_at',
            ])
            ->when($request->name, fn(Builder $builder, $name) => $builder->whereAny(['slug->ar', 'slug->en'], 'like', "%{$name}%"))


            // ->orderBy('id' , 'ASC')
            ->oldest('id')
            ->paginate(pagination());



        // $breadcrumbs = Breadcrumbs::render('roles');


        return Inertia::render('AdminsAndRoles/Roles/Index', [

            'title' => 'roles and permissions',
            'items' => RoleResource::collection($roles),
            'headers' => [
                [
                    'label' => '#',
                    'name' => '#',
                ],
                [
                    'label' => 'name',
                    'name' => 'name', ////// make it like the name in database because we will use it in filters
                ],
                [
                    'label' => 'created_at',
                    'name' => 'created_at',
                ],
                [
                    'label' => 'actions',
                    'name' => 'actions',
                ],
            ],
            // 'breadcrumbs' => $breadcrumbs['breadcrumbs'],

            'filters' => (object) $request->all(),
            'routeResourceName' => $this->routeResourceName,
            'method' => 'index',
            'can' => [
                'create' => true,
                // 'create' => $request->user()->can('create role'),
            ],
        ]);
    }

    public function create()
    {
        // $breadcrumbs = Breadcrumbs::render('newRole');

        return Inertia::render('AdminsAndRoles/Roles/Create', [
            'edit' => false,
            'title' => 'add role',
            'routeResourceName' => $this->routeResourceName,
            // 'breadcrumbs' => $breadcrumbs['breadcrumbs'],

        ]);
    }

    public function store(RolesRequest $request)
    {

        // ddd($request);
        $data["name"] = $request->name;
        $data["slug"]["ar"] = $request->slug['ar'];
        $data["slug"]["en"] = $request->slug['en'];

        $data["guard_name"] = 'web';

        $role = Role::create($data);

        return redirect()->route($this->routeResourceName . '.edit', $role)->with('success', 'Role created successfully.');
    }


    public function edit(Role $role)
    {
        $role->load(['permissions:permissions.id,permissions.name']);
        // $breadcrumbs = Breadcrumbs::render('editRole');

        return Inertia::render('AdminsAndRoles/Roles/Create', [
            'edit' => true,
            'title' => 'edit role and permissions',
            // 'breadcrumbs' => $breadcrumbs['breadcrumbs'],

            'item' => new RoleResource($role),
            'routeResourceName' => $this->routeResourceName,
            // 'permissions' => PermissionResource::collection(Permission::oldest('id')->get(['id', 'name'])),
            // 'pagesPermissions' => PagePermission::all(),
            'pagesPermissions' => PagePermissionResource::collection(PagePermission::where('type', 1)->get(['id', 'name', 'permissions'])),
            'specialPermissions' => PagePermissionResource::collection(PagePermission::where('type', 2)->get(['id', 'name', 'permissions'])),
        ]);
    }

    public function update(RolesRequest $request, Role $role)
    {
        $data["name"] = $request->name;
        $data["slug"]["ar"] = $request->slug['ar'];
        $data["slug"]["en"] = $request->slug['en'];

        $role->update($data);

        return redirect()->route($this->routeResourceName . '.index')->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        // dd($role);
        DB::beginTransaction();
        try {


            // if ($role->name == 'Super Admin') {
            if ($role->id == 1) {
                abort(403, 'general.can not delete super admin role');
            }

            if (!auth()->user()->can('delete user') || $role->used_before == true) {
                abort(403, 'general.you can not delete an item that has previous activity on the system or you do not have permission');
            }


            $role->delete();

            DB::commit();
            return redirect()->back()->with('success', 'item deleted successfully');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
