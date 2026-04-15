<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\PagePermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds. 
     *
     * @return void
     */
    public function run()
    {


        Schema::disableForeignKeyConstraints();

        Role::truncate();
        Permission::truncate();
        PagePermission::truncate();

        Schema::enableForeignKeyConstraints();


        $superAdminRole = Role::where('name', '=', 'Super Admin')->first();
        $superAdminRole != null ? $superAdminRole->delete() : '';


        $data['name'] = 'Super Admin';
        $data['slug']['ar'] = 'المدير العام';
        $data['slug']['en'] = 'Super Admin';

        $data["guard_name"] = 'web';

        // dd($data) ;

        $superAdmin = Role::create($data);

        User::find(1)->assignRole($superAdmin);






        // $employeeRole = Role::where('name', '=', 'Employee')->first();

        // $employeeRole !== null ? $employeeRole->delete() : '';

        // $employeeData['name'] = 'Employee';
        // $employeeData['slug']['ar'] = 'موظف';
        // $employeeData['slug']['en'] = 'Employee';

        // $data["guard_name"] = 'web';

        // $employeeRole = Role::create($employeeData);

 


        $nationalitiesPagePermissions = [
            'view nationalities',
            'create nationality',
            'edit nationality',
            'delete nationality',
        ];
        $nationalitiesPagePermissionsIds = [];
        foreach ($nationalitiesPagePermissions as $item) {
            $permission = Permission::create(['name' => $item]);

            $firstWord = explode(' ', trim($item))[0];
            $nationalitiesPagePermissionsIds[$firstWord] = $permission->id;
        }
        $nationalitiesPageData['name']['en'] = 'nationalities';
        $nationalitiesPageData['name']['ar'] = 'الجنسيات';
        $nationalitiesPageData['permissions'] = $nationalitiesPagePermissionsIds;
        PagePermission::create($nationalitiesPageData);




        


        $rolesPagePermissions = [
            'view roles',
            'create role',
            'edit role',
            'delete role',
        ];
        $rolesPagePermissionsIds = [];
        foreach ($rolesPagePermissions as $item) {
            $permission = Permission::create(['name' => $item]);

            $firstWord = explode(' ', trim($item))[0];
            $rolesPagePermissionsIds[$firstWord] = $permission->id;
        }
        $rolesPageData['name']['en'] = 'roles';
        $rolesPageData['name']['ar'] = 'الصلاحيات';
        $rolesPageData['permissions'] = $rolesPagePermissionsIds;
        PagePermission::create($rolesPageData);





                ////////////////////// special permissions ////////////////////

        // بداية المتغيرات اختصار للاسم
        // variables starts with name shortcut
        
        
                $VSPermission = Permission::create(['name' => 'view system settings']);
                $VSPageData['name']['en'] = 'view system settings';
                $VSPageData['name']['ar'] = 'عرض اعدادات النظام';
                $VSPageData['permissions']['id'] = $VSPermission->id;
                $VSPageData['type'] = 2;
                PagePermission::create($VSPageData);

        $ESPermission = Permission::create(['name' => 'edit system settings']);
        $ESPageData['name']['en'] = 'edit system settings';
        $ESPageData['name']['ar'] = 'تعديل اعدادات النظام';
        $ESPageData['permissions']['id'] = $ESPermission->id;
        $ESPageData['type'] = 2;
        PagePermission::create($ESPageData);
        






        $adminsPagePermissions = [
            'view admins',
            'create admin',
            'edit admin',
            'delete admin',
        ];
        $adminsPagePermissionsIds = [];
        foreach ($adminsPagePermissions as $item) {
            $permission = Permission::create(['name' => $item]);

            $firstWord = explode(' ', trim($item))[0];
            $adminsPagePermissionsIds[$firstWord] = $permission->id;
        }
        $adminsPageData['name']['en'] = 'admins';
        $adminsPageData['name']['ar'] = 'المدراء';
        $adminsPageData['permissions'] = $adminsPagePermissionsIds;
        PagePermission::create($adminsPageData);


        $usersPagePermissions = [
            'view users',
            'create user',
            'edit user',
            'delete user',
        ];
        $usersPagePermissionsIds = [];
        foreach ($usersPagePermissions as $item) {
            $permission = Permission::create(['name' => $item]);

            $firstWord = explode(' ', trim($item))[0];
            $usersPagePermissionsIds[$firstWord] = $permission->id;
        }
        $usersPageData['name']['en'] = 'users';
        $usersPageData['name']['ar'] = 'المستخدمون';
        $usersPageData['permissions'] = $usersPagePermissionsIds;
        PagePermission::create($usersPageData);


    }
}
