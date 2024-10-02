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
        ]);

      
        // Asegurarse de que el rol de admin ya exista
        $role = Role::where('name', 'admin')->first();

        if ($role) {
            // Asignar el rol de admin
            $admin->assignRole('admin');
        } else {
            // Si el rol no existe, se crea y se asigna
            $admin->assignRole(Role::create(['name' => 'admin']));
        }
    }
}
