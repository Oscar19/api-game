<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'), 
            'rol' => 'admin',
        ]);

        
        $role = Role::where('name', 'admin')->first();

        if ($role) {
           
            $admin->assignRole('admin');
        } else {
          
            $admin->assignRole(Role::create(['name' => 'admin']));
        }
    }
}
