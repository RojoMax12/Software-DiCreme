<?php

namespace Database\Factories;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Formato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente al Factory.
     *
     * @var string
     */
    protected $model = Producto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Mapeo exacto de los sabores por su categoría correspondiente
        $saboresPorCategoria = [
            'Al agua' => [
                'Piña',
                'Limón Manzana',
                'Chirimoya Alegre',
                'Limón menta jengibre',
            ],
            'Leche de avena' => [
                'Piña Colada',
                'Melón Tuna',
                'Frambuesa',
                'Chirimoya',
            ],
            'Tradicional' => [
                'Chocolate',
                'Tropical',
                'Frutos del Bosque',
                'Pie de Limón',
                'Cookies and cream',
                'Menta Chips',
                'Vainilla',
                'Lúcuma',
                'Cola de Mono',
                'Pistacho',
                'Pasas al Ron',
                'Mora',
                'Suspiro Limeño',
                'Chocolate Avellana',
                'Cheesecake frutos rojos',
                'Frutilla',
                'Dulce de Leche',
                'Banana Split',
                'Mango',
                'Arroz con leche',
                'Tres leches',
            ],
            'Sin azúcar' => [
                'Chocolate sin azúcar',
                'Pistacho sin azúcar',
                'Frutos del Bosque sin azúcar',
                'Vainilla',
            ],
        ];

        // 1. Obtener una categoría aleatoria de las existentes
        $categoriaSeleccionada = Categoria::whereIn('nombre_categoria', array_keys($saboresPorCategoria))
            ->inRandomOrder()
            ->first();

        $idCategoria = $categoriaSeleccionada?->id ?? Categoria::factory();
        $nombreCategoria = $categoriaSeleccionada?->nombre_categoria ?? 'Tradicional';

        // 2. Obtener un formato aleatorio de la base de datos (10 Litros, 5 Litros, etc.)
        $formatoSeleccionado = Formato::whereIn('nombre_formato', ['10 Litros', '5 Litros', '2.5 Litros', '1 Litro'])
            ->inRandomOrder()
            ->first();

        $idFormato = \App\Models\Formato::inRandomOrder()->first()?->id ?? \App\Models\Formato::first()?->id;
        
        // 3. Seleccionar el sabor correspondiente a la categoría elegida
        $saboresDisponibles = $saboresPorCategoria[$nombreCategoria] ?? $saboresPorCategoria['Tradicional'];
        $saborAleatorio = $this->faker->randomElement($saboresDisponibles);

        return [
            'id_categoria'    => $idCategoria,
            'id_formato'      => $idFormato,
            'nombre_producto' => $saborAleatorio, // Ahora guarda exclusivamente el nombre del sabor (Ej: "Menta Chips")
            'precio_producto' => $this->faker->numberBetween(2500, 18000),
        ];
    }
}