<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\PagePermission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\RolesRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\PagePermissionResource;
use App\QueryBuilders\RoleQueryBuilder;
use App\Actions\Role\CreateRoleAction;
use App\Actions\Role\UpdateRoleAction;
use App\Actions\Role\DeleteRoleAction;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RolesController extends Controller implements HasMiddleware
{
    private string $routeResourceName = 'roles';

    public static function middleware(): array
    {
        return [
            new Middleware('can:view roles', only: ['index']),
            new Middleware('can:create role', only: ['create', 'store']),
            new Middleware('can:edit role', only: ['edit', 'update']),
            new Middleware('can:delete role', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $roles = RoleQueryBuilder::forRequest($request)
            ->paginate(pagination());

        return Inertia::render('AdminsAndRoles/Roles/Index', [
            'title'             => 'roles',
            'items'             => RoleResource::collection($roles),
            'headers'           => $this->indexHeaders(),
            'filters'           => (object) $request->all(),
            'routeResourceName' => $this->routeResourceName,
            'method'            => 'index',
            'can' => [
                'create' => true,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('AdminsAndRoles/Roles/Create', [
            'edit'              => false,
            'title'             => 'add role',
            'routeResourceName' => $this->routeResourceName,
        ]);
    }

    public function store(RolesRequest $request, CreateRoleAction $action): RedirectResponse
    {
        $role = $action->execute($request->validated());

        return redirect()
            ->route($this->routeResourceName . '.edit', $role)
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): Response
    {
        $role->load(['permissions:permissions.id,permissions.name']);

        return Inertia::render('AdminsAndRoles/Roles/Create', [
            'edit'               => true,
            'title'              => 'edit role and permissions',
            'item'               => new RoleResource($role),
            'routeResourceName'  => $this->routeResourceName,
            'pagesPermissions'   => PagePermissionResource::collection(
                PagePermission::where('type', 1)->get(['id', 'name', 'permissions'])
            ),
            'specialPermissions' => PagePermissionResource::collection(
                PagePermission::where('type', 2)->get(['id', 'name', 'permissions'])
            ),
        ]);
    }

    public function update(RolesRequest $request, Role $role, UpdateRoleAction $action): RedirectResponse
    {
        $action->execute($role, $request->validated());

        return redirect()
            ->route($this->routeResourceName . '.index')
            ->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role, DeleteRoleAction $action): RedirectResponse
    {
        $action->execute($role);

        return redirect()->back()->with('success', 'item deleted successfully');
    }

    private function indexHeaders(): array
    {
        return collect(['#', 'name', 'created_at', 'actions'])
            ->map(fn ($label) => ['label' => $label, 'name' => $label])
            ->all();
    }
}