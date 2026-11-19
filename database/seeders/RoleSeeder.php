<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    
    public function run(): void
    {
        // Create roles
       // 1. limpiar cache de permisos
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. crear roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);

        // 3. Crear Usuario Admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@tienda.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'), // Cambia la contraseña según tus necesidades
            ]
        );


        $adminUser->assignRole($adminRole);


        // 4. Crear Usuario Cliente
        $clientUser = User::firstOrCreate(
            ['email' => 'cliente@tienda.com'],
            [
                'name' => 'Cliente',
                'password' => Hash::make('cliente123'), // Cambia la contraseña según tus necesidades
            ]
        );

        $clientUser->assignRole($clientRole);
    }
}


