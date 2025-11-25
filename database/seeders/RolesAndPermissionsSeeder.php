<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos por seguridad
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear los roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $clienteRole = Role::firstOrCreate(['name' => 'cliente']);
        // NUEVO: Rol para el agente de ventas
        $agenteRole = Role::firstOrCreate(['name' => 'agente-ventas']); 
        
        // 2. Definir permisos
        $adminPermissions = [
            'user-list', 'user-create', 'user-edit', 'user-delete', 'user-activate',
            'rol-list', 'rol-create', 'rol-edit', 'rol-delete',
            'producto-list', 'producto-create', 'producto-edit', 'producto-delete',
            'pedido-list', 'pedido-cancel', 'pedido-anulate', 
            // Permisos CRUD para la nueva funcionalidad de Clientes (solo Admin)
            'cliente-list', 'cliente-create', 'cliente-edit', 'cliente-delete',
        ];

        $clientePermissions = ['pedido-view', 'pedido-cancel', 'perfil'];

        // Permisos para el Agente de Ventas
        $agentePermissions = [
            'pedido-list',           // Para ver los pedidos de sus clientes
            'pedido-create-cliente', // Permiso clave: crear pedidos a nombre de un cliente
            'perfil',
            // El agente necesita listar sus clientes
            'cliente-list',
            'pedido-view',
            'pedido-cancel',
            'producto-list',
        ];

        // 3. Crear todos los permisos y asignar a los roles

        // Combinar todos los permisos únicos
        $allPermissions = array_unique(array_merge($adminPermissions, $clientePermissions, $agentePermissions));

        foreach ($allPermissions as $permiso) {
            $permission = Permission::firstOrCreate(['name' => $permiso]);
            
            // Asignar a Admin
            if (in_array($permiso, $adminPermissions)) {
                $adminRole->givePermissionTo($permission);
            }
            
            // Asignar a Agente de Ventas
            if (in_array($permiso, $agentePermissions)) {
                $agenteRole->givePermissionTo($permission);
            }
            
            // Asignar a Cliente
            if (in_array($permiso, $clientePermissions)) {
                $clienteRole->givePermissionTo($permission);
            }
        }

        // 4. Crear usuarios de prueba y asignar roles

        // Usuario Administrador
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@prueba.com'],
            ['name' => 'Admin', 'password' => Hash::make('admin123456')] // Usar Hash::make
        );
        $adminUser->assignRole($adminRole);

        // Usuario Cliente
        $clienteUser = User::firstOrCreate(
            ['email' => 'cliente@prueba.com'],
            ['name' => 'Cliente', 'password' => Hash::make('cliente123456')] 
        );
        $clienteUser->assignRole($clienteRole);
        
        // NUEVO: Usuario Agente de Ventas
        $agenteUser = User::firstOrCreate(
            ['email' => 'agente@prueba.com'],
            ['name' => 'Agente de Ventas', 'password' => Hash::make('agente123')] 
        );
        $agenteUser->assignRole($agenteRole);
    }
}