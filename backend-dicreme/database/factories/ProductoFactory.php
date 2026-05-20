<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categorias = [
            'Normales',
            'Premium',
            'Vegano',
            'Sin Azúcar',
            'Sin Lactosa',
        ];

        $sabores = [
            'Vainilla',
            'Chocolate',
            'Frutilla',
            'Menta Chips',
            'Dulce de Leche',
            'Maracuyá',
            'Cookies & Cream',
            'Manjar',
            'Piña Colada',
            'Berries Mix',
        ];

        $formatos = ['10L', '5L', '1L'];

        $categoria = \App\Models\Categoria::whereIn('nombre_categoria', $categorias)
            ->inRandomOrder()
            ->first()?->id
            ?? \App\Models\Categoria::factory();

        $formato = \App\Models\Formato::whereIn('nombre_formato', $formatos)
            ->inRandomOrder()
            ->first()?->id
            ?? \App\Models\Formato::factory();

        return [
            'id_categoria' => $categoria,
            'id_formato' => $formato,
            'nombre_producto' => $this->faker->randomElement($sabores).' '.$this->faker->randomElement($formatos),
            'precio_producto' => $this->faker->numberBetween(2500, 18000),
        ];
    }
}
