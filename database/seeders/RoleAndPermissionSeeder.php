<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos de ejemplo o los de Shield si ya están definidos
        $permissions = [
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'view_roles',
            'manage_roles',
            'access_filament',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear el rol de super_admin si no existe
        $role = Role::firstOrCreate(['name' => 'super_admin']);

        // Asignar todos los permisos al rol super_admin
        $role->syncPermissions(Permission::all());

        // Asignar el rol super_admin al usuario admin
        $user = User::where('email', 'admin@admin.com')->first();
        if ($user && !$user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }
    }
}
