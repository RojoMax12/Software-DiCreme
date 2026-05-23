<?php

namespace Database\Factories;

use App\Models\Cotizacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cotizacion>
 */
class CotizacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

                'id_usuario_dicreme' => \App\Models\Usuario_dicreme::inRandomOrder()->first()?->id 
                                ?? \App\Models\Usuario_dicreme::factory(),
                'id_distribuidor' => \App\Models\Usuario_distribuidores::inRandomOrder()->first()?->id 
                                ?? \App\Models\Usuario_distribuidores::factory(),
                'estado_cotizacion' => $this->faker->randomElement(['Pendiente', 'Aprobada', 'Rechazada']),
                'fecha_creacion' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),   

            //
        ];
    }
}
