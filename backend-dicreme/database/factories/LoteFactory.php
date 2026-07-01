<?php

namespace Database\Factories;

use App\Models\Lote;
use App\Models\Producto;
use App\Models\Bodega;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoteFactory extends Factory
{
    protected $model = Lote::class;

    public function definition(): array
    {
        // Generamos la cantidad primero para poder reutilizarla
        $cantidad = $this->faker->numberBetween(20, 400);

        return [
            // Si existen productos/bodegas, usa uno al azar, sino crea uno nuevo
            'id_producto' => Producto::inRandomOrder()->first()?->id ?? Producto::factory(),
            'id_bodega'   => Bodega::inRandomOrder()->first()?->id ?? Bodega::factory(),
            
            'cantidad_producida' => $cantidad,
            
            // Usamos la variable $cantidad que definimos arriba
            'cantidad_producto'  => $cantidad, 
            
            'fecha_emision'     => $this->faker->dateTimeBetween('-30 days', 'now'),
            'fecha_vencimiento' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
        ];
    }
}