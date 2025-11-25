<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\User; // Para asignar al admin

class ClienteGeneralSeeder extends Seeder
{
    public function run(): void
    {
        // Busca al primer usuario admin (o ajusta para encontrar tu admin principal)
        $adminUser = User::role('admin')->first(); 

        if ($adminUser) {
            Cliente::firstOrCreate(
                ['codigo' => 'GENERAL'], // Código único para identificarlo fácil
                [
                    'nombre' => 'CLIENTE GENERAL / COTIZACIÓN',
                    'contacto' => 'N/A',
                    'telefono' => 'N/A',
                    'email' => null, // email genérico si se prefiere, pero debe ser único o null
                    'direccion' => 'N/A',
                    'activo' => true,      // Debe estar activo para aparecer
                    'user_id' => $adminUser->id, // Asignado al admin
                    'descuento' => 0.00,   // Sin descuento por defecto
                ]
            );
        } else {
            // Opcional: Manejar el caso si no se encuentra admin
            $this->command->warn('Usuario Admin no encontrado. No se pudo crear el Cliente General.');
        }
    }
}