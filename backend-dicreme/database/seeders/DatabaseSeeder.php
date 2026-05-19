<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = ['Admin', 'Trabajador', 'Distribuidor'];
        $estadosPedido = ['Validacion', 'Preparacion', 'Despachado', 'Entregado'];

        foreach ($roles as $rol) {
            \App\Models\Rol::firstOrCreate(['nombre_rol' => $rol]);
        }

        foreach ($estadosPedido as $estado) {
            \App\Models\Estado_pedido::firstOrCreate(['nombre_estado' => $estado]);
        }

        # Crear un usuario admin si no existe
        $adminRole = \App\Models\Rol::where('nombre_rol', 'Admin')->first();

        if ($adminRole) {
            \App\Models\Usuario_dicreme::firstOrCreate(
                ['correo_electronico' => 'admin@dicreme.cl'],
                [
                    'nombre_usuario' => 'admin',
                    'contrasena' => 'Admin1234',
                    'id_rol' => $adminRole->id,
                ]
            );
        }

        \App\Models\Usuario_dicreme::factory(10)->create();
        \App\Models\Usuario_distribuidores::factory(10)->create();
        \App\Models\Pedido::factory(20)->create();
        \App\Models\Venta::factory(20)->create();
        \App\Models\Despacho::factory(20)->create();
    }
}



