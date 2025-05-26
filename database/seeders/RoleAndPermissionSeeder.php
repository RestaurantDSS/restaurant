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
        // Crear permisos de ejemplo o losy de Shield si ya están definidos
        $permissions = [
            'view_categoria',
            'view_any_categoria',
            'create_categoria',
            'update_categoria',
            'restore_categoria',
            'restore_any_categoria',
            'replicate_categoria',
            'reorder_categoria',
            'delete_categoria',
            'delete_any_categoria',
            'force_delete_categoria',
            'force_delete_any_categoria',
            'view_cliente',
            'view_any_cliente',
            'create_cliente',
            'update_cliente',
            'restore_cliente',
            'restore_any_cliente',
            'replicate_cliente',
            'reorder_cliente',
            'delete_cliente',
            'delete_any_cliente',
            'force_delete_cliente',
            'force_delete_any_cliente',
            'view_inventario',
            'view_any_inventario',
            'create_inventario',
            'update_inventario',
            'restore_inventario',
            'restore_any_inventario',
            'replicate_inventario',
            'reorder_inventario',
            'delete_inventario',
            'delete_any_inventario',
            'force_delete_inventario',
            'force_delete_any_inventario',
            'view_pe::detalle',
            'view_any_pe::detalle',
            'create_pe::detalle',
            'update_pe::detalle',
            'restore_pe::detalle',
            'restore_any_pe::detalle',
            'replicate_pe::detalle',
            'reorder_pe::detalle',
            'delete_pe::detalle',
            'delete_any_pe::detalle',
            'force_delete_pe::detalle',
            'force_delete_any_pe::detalle',
            'view_pedido',
            'view_any_pedido',
            'create_pedido',
            'update_pedido',
            'restore_pedido',
            'restore_any_pedido',
            'replicate_pedido',
            'reorder_pedido',
            'delete_pedido',
            'delete_any_pedido',
            'force_delete_pedido',
            'force_delete_any_pedido',
            'view_producto',
            'view_any_producto',
            'create_producto',
            'update_producto',
            'restore_producto',
            'restore_any_producto',
            'replicate_producto',
            'reorder_producto',
            'delete_producto',
            'delete_any_producto',
            'force_delete_producto',
            'force_delete_any_producto',
            'view_role',
            'view_any_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',
            'view_user',
            'view_any_user',
            'create_user',
            'update_user',
            'restore_user',
            'restore_any_user',
            'replicate_user',
            'reorder_user',
            'delete_user',
            'delete_any_user',
            'force_delete_user',
            'force_delete_any_user'
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
