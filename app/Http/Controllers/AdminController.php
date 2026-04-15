<?php

namespace App\Http\Controllers;


use App\Models\Role;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Inertia\Inertia;
use App\Models\Setting;
use App\Models\Admin;

use Illuminate\Http\Request;
use App\Models\PagePermission;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AdminRequest;
// use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\PagePermissionResource;
use Illuminate\Routing\Controllers\Middleware;
class AdminController extends Controller implements HasMiddleware
{

    private string $routeResourceName = 'admins';


    // public function __construct()
    // {
    //     $this->middleware('can:view admins')->only('index', 'show');
    //     $this->middleware('can:create admin')->only(['store']);
    //     $this->middleware('can:edit admin')->only(['update']);
    //     $this->middleware('can:delete admin')->only('destroy');
    // }

        public static function middleware(): array
    {
        return [
            // Apply 'can' middleware to specific methods
            new Middleware('can:view admins', only: ['index' , 'show']),
            new Middleware('can:create admin', only: ['store']),
            new Middleware('can:edit admin', only: ['update']),
            new Middleware('can:delete admin', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        // ddd($this);
        // dd($request->active);
        $users = User::query()
            ->select([
                'id',
                'name',
                'active',
                'email',
                'used_before',
                'profile_type',
                'profile_id',
                'created_at',
            ])
            ->with(['roles:roles.id,roles.name'])
            ->with('media')

            ->when($request->name, fn(Builder $builder, $name) => $builder->whereAny(['name->ar', 'name->en'], 'like', "%{$name}%"))


            ->when(
                $request->active !== null,
                fn(Builder $builder) => $builder->when(
                    $request->active,
                    fn(Builder $builder) => $builder->active(),
                    fn(Builder $builder) => $builder->inActive()
                )
            )



            ->latest('id')

            ->where('id', '<>', auth()->user()->id)


            ->paginate(pagination())->onEachSide(1)->appends(request()->query());


            


        // $breadcrumbs = Breadcrumbs::render('admins');


        return Inertia::render('AdminsAndRoles/Admins/Index', [
            'title' => 'system admins',
            'items' => UserResource::collection($users),
            'roles' => RoleResource::collection(Role::where('id', '!=', 1)->get()),
            // 'roles' => RoleResource::collection(Role::where('name', '!=', 'Super Admin')->get()),

            'headers' => [
                [
                    'label' => '#',
                    'name' => '#',
                ],

                [
                    'label' => 'name',
                    'name' => 'name',
                ],
                [
                    'label' => 'active',
                    'name' => 'active',
                ],


                [
                    'label' => 'phone',
                    'name' => 'phone',
                ],
                [
                    'label' => 'created at',
                    'name' => 'created at',
                ],
                [
                    'label' => 'actions',
                    'name' => 'actions',
                ],
            ],
            'filters' => (object) $request->all(),
            'routeResourceName' => $this->routeResourceName,

            'can' => [
                'view' => $request->user()->can('view admin'),
                'create' => $request->user()->can('create admin'),
                'delete' => $request->user()->can('delete admin'),
            ],

            'method' => 'index',
            // 'breadcrumbs' => $breadcrumbs['breadcrumbs'],


        ]);
    }




    public function show($id)
    {

        $user = User::with(['roles', 'permissions'])->where('id', '<>', 1)->findOrFail($id); // to prevent showint super admin  data

        $role = Role::find($user->roles[0]['id']);

        $role->load(['permissions:permissions.id,permissions.name']);

        // $breadcrumbs = Breadcrumbs::render('adminData', $user);


        return Inertia::render('AdminsAndRoles/Admins/Show', [

            'title' => 'admin data',
            'role' => new RoleResource($role),
            'item' => new UserResource($user),
            'specialPermissions' => PagePermissionResource::collection(PagePermission::where('type', 2)->get(['id', 'name', 'permissions'])),

            'can' => [
                'editRole' => auth()->user()->can('edit role'),
            ],

            'headers' => [
                [
                    'label' => '#',
                    'name' => '#',
                ],

                [
                    'label' => 'name',
                    'name' => 'name',
                ],
                [
                    'label' => 'active',
                    'name' => 'active',
                ],

                // [
                //     'label' => 'role',
                //     'name' => 'role',
                // ],
                [
                    'label' => 'created at',
                    'name' => 'created at',
                ],
                [
                    'label' => 'actions',
                    'name' => 'actions',
                ],
            ],
            'method' => 'index',
            // 'breadcrumbs' => $breadcrumbs['breadcrumbs'],

        ]);
    }






    public function store(AdminRequest $request)
    {
        try {
            $data = $request->safe()->only([
                'email',
                'active',
            ]);
            $data["name"]["ar"] = $request->name['ar'];
            $data["name"]["en"] = $request->name['en'];
            $data['active'] = $request->boolean('active');
            $data["password"] = Hash::make($request->safe()->password);
            $user = User::create($data);
            
            // dd($request);
            // admin code
            
            $adminData = [
                'added_by' => auth()->user()->id,
                'phone' => $request->phone,
            ];
            
            $admin = Admin::create($adminData);
            
            // to add profile to the user we have two options 
            $admin->user()->save($user); 

            // $user->profile_id = $admin->id ;
            // $user->profile_type = 'App\Models\Admin' ;
            // $user->save();
            
            
            
            
            // update used before field
            $role = Role::find($request->roleId);
            $role->used_before = true;
            $role->save();
            // $user->assignRole($request->roleId);
            $user->assignRole($role);

            DB::commit();
            // dd('$role');
            return redirect()->back()->with('success', 'item created successfully');
        } catch (\Throwable $th) {
            // dd($th->getMessage());
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }



    public function update(AdminRequest $request, User $user)
    {

        try {
            DB::beginTransaction();

            // $user = User::find($id);
            $data = $request->safe()->only(['email', 'active']);

            $request->password !== null ? $data['password'] = bcrypt($request->safe()->password) : '';

            $data["name"]["ar"] = $request->name['ar'];
            $data["name"]["en"] = $request->name['en'];

            $user->update($data);

            $admin = Admin::find($user->profile->id);
            if ($admin !== null) {

                $admin->update([
                    'phone' => $request->phone,
                    'updated_by' => auth()->user()->id,
                ]);
            }

            
            // update used before field
            $role = Role::find($request->roleId);
            $role->used_before = true;
            $role->save();
            
            $user->syncRoles($role);
            DB::commit();
            return back()->with('success', 'item updated successfully');
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->back()->with('error', $th->getMessage());
        }
    }



    public function destroy($ids)
    {
        try {
            DB::beginTransaction();
            $all_ids = explode(',', $ids);
            foreach ($all_ids as $id) {
                $user = User::find($id);
                $admin = Admin::find($user->profile_id);

                
                if (!auth()->user()->can('delete admin') || $user->used_before == true) {
                    abort(403, 'general.you can not delete an item that has previous activity on the system or you do not have permission');
                }
                
                if (!empty($admin)) {
                    // continue;
                    $admin->delete();
                }
                $user->delete();


            }

            DB::commit();
            return redirect()->back()->with('success', 'item deleted successfully');
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

}
