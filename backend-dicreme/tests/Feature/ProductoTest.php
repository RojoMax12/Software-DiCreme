<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Formato;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $categoria = Categoria::create([
            'nombre_categoria' => 'Lácteos',
            'descripcion' => 'Productos lácteos artesanales'
        ]);

        $formato = Formato::create([
            'nombre_formato' => 'Frasco 500g'
        ]);

        Producto::create([
            'id_categoria' => $categoria->id,
            'id_formato' => $formato->id,
            'nombre_producto' => 'Manjar Casero 500g',
            'precio' => 3500,
            'estado_producto' => 1
        ]);
    }

    public function test_listar_productos_retorna_lista_exitosa(): void
    {
        $response = $this->getJson('/api/productos');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data'
                 ]);
    }

    public function test_obtener_resumen_totales_productos(): void
    {
        $response = $this->getJson('/api/productos/resumen_totales');

        $response->assertStatus(200);
    }
}
