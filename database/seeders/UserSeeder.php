<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data['email'] =  'admin@gmail.com'  ;
        $data['password'] =  bcrypt('55555sssss') ;
        $data['name']['en'] = 'Super Admin' ;
        $data['name']['ar'] = 'المدير العام' ;
        
        $user = User::create($data);



            // $admin = Admin::create([
            //     'added_by' => 1 ,
            //     'updated_by' => 1 ,
            // ]);
            // $admin->user()->save($user);

            // $user->assignRole('Super Admin'); 

    }
}
