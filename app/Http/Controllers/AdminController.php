<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\PagePermission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\AdminRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\PagePermissionResource;
use App\QueryBuilders\AdminUserQueryBuilder;
use App\Actions\Admin\CreateAdminAction;
use App\Actions\Admin\UpdateAdminAction;
use App\Actions\Admin\DeleteAdminsAction;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminController extends Controller implements HasMiddleware
{
    private string $routeResourceName = 'admins';

    public static function middleware(): array
    {
        return [
            new Middleware('can:view admins', only: ['index', 'show']),
            new Middleware('can:create admin', only: ['store']),
            new Middleware('can:edit admin', only: ['update']),
            new Middleware('can:delete admin', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $users = AdminUserQueryBuilder::forRequest($request)
            ->paginate(pagination())
            ->onEachSide(1)
            ->appends($request->query());

        return Inertia::render('AdminsAndRoles/Admins/Index', [
            'title'             => 'system admins',
            'items'             => UserResource::collection($users),
            'roles'             => RoleResource::collection(Role::where('id', '!=', 1)->get()),
            'headers'           => $this->indexHeaders(),
            'filters'           => (object) $request->all(),
            'routeResourceName' => $this->routeResourceName,
            'can' => [
                'view'   => $request->user()->can('view admin'),
                'create' => $request->user()->can('create admin'),
                'delete' => $request->user()->can('delete admin'),
            ],
            'method' => 'index',
        ]);
    }

    public function show(int $id): Response
    {
        $user = User::with(['roles', 'permissions'])
            ->where('id', '<>', 1) // prevent viewing super admin data
            ->findOrFail($id);

        $role = Role::with(['permissions:permissions.id,permissions.name'])
            ->findOrFail($user->roles[0]['id']);

        return Inertia::render('AdminsAndRoles/Admins/Show', [
            'title'              => 'admin data',
            'role'               => new RoleResource($role),
            'item'               => new UserResource($user),
            'specialPermissions' => PagePermissionResource::collection(
                PagePermission::where('type', 2)->get(['id', 'name', 'permissions'])
            ),
            'can' => [
                'editRole' => auth()->user()->can('edit role'),
            ],
            'headers' => $this->showHeaders(),
            'method'  => 'index',
        ]);
    }

    public function store(AdminRequest $request, CreateAdminAction $action): RedirectResponse
    {
        $action->execute($request->validated(), (int) $request->roleId);

        return redirect()->back()->with('success', 'item created successfully');
    }

    public function update(AdminRequest $request, User $user, UpdateAdminAction $action): RedirectResponse
    {
        $action->execute($user, $request->validated(), (int) $request->roleId);

        return back()->with('success', 'item updated successfully');
    }

    public function destroy( $ids, DeleteAdminsAction $action): RedirectResponse
    {
        $ids = array_map('intval', explode(',', $ids));

        $action->execute($ids);

        return redirect()->back()->with('success', 'item deleted successfully');
    }

    private function indexHeaders(): array
    {
        return collect(['#', 'name', 'active', 'phone', 'created at', 'actions'])
            ->map(fn ($label) => ['label' => $label, 'name' => $label])
            ->all();
    }

    private function showHeaders(): array
    {
        return collect(['#', 'name', 'active', 'created at', 'actions'])
            ->map(fn ($label) => ['label' => $label, 'name' => $label])
            ->all();
    }
}