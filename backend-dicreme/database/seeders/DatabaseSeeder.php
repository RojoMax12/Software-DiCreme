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
        $roles = ['Admin', 'Dicreme', 'Distribuidor', 'Despachador'];
        $estadosPedido = ['Validacion', 'Preparacion', 'Listo para Despacho', 'En Despacho','Entregado', 'Pendiente', 'Cancelado'];
        $estadosDespacho = ['Pendiente asignacion', 'Asignado', 'En ruta', 'Entrega exitosa', 'Intento Fallido', 'Retornado a bodega'];
        $estadosPago = ['Por pagar','Pagado'];
        $estadosCotizacion = ['Por Tomar','En Revision', 'Completado', 'Cancelado'];
        $categorias = ['Al agua', 'Leche de avena', 'Tradicional', 'Sin azúcar'];
        $formatos = ['10L', '5L', '2.5L', '1L'];
        
        foreach ($estadosCotizacion as $estado){
        \App\Models\Estado_cotizacion::firstOrCreate(['nombre_estado' => $estado]);
        }

        foreach ($estadosDespacho as $estado){
        \App\Models\Estado_despacho::firstOrCreate(['nombre_estado' => $estado]);
        }
        
        foreach ($roles as $rol) {
            \App\Models\Rol::firstOrCreate(['nombre_rol' => $rol]);
        }

        foreach ($estadosPedido as $estado) {
            \App\Models\Estado_pedido::firstOrCreate(['nombre_estado' => $estado]);
        }
        
        foreach($estadosPago as $estado){
            \App\Models\Estado_pago::firstOrCreate(['nombre_estado' => $estado]);
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

        foreach ($categorias as $categoria) {
            \App\Models\Categoria::firstOrCreate(
                ['nombre_categoria' => $categoria],
                ['descripcion_categoria' => 'Categoría de helados '.$categoria]
            );
        }

        $formatosConPrecio = [
            ['nombre_formato' => '10L',  'precio_formato' => 23900, 'imagen_formato' => '/storage/formatos/10L.webp'],
            ['nombre_formato' => '5L',   'precio_formato' => 16900, 'imagen_formato' => '/storage/formatos/5L.webp'],
            ['nombre_formato' => '2.5L', 'precio_formato' => 8900,  'imagen_formato' => '/storage/formatos/2.5L.webp'],
            ['nombre_formato' => '1L',   'precio_formato' => 3900,  'imagen_formato' => '/storage/formatos/1L.webp'],
        ];

        foreach ($formatosConPrecio as $f) {
            \App\Models\Formato::updateOrCreate(
                ['nombre_formato' => $f['nombre_formato']],
                [
                    'precio_formato' => $f['precio_formato'],
                    'imagen_formato' => $f['imagen_formato']
                ]
            );
        }

        \App\Models\Bodega::factory(5)->create();
        \App\Models\Producto::factory(132)->create();
        \App\Models\Lote::factory(25)->create();

        \App\Models\Usuario_dicreme::factory(10)->create();
        \App\Models\Usuario_distribuidores::factory(10)->create();
        \App\Models\Pedido::factory(30)->create();
        \App\Models\Venta::factory(20)->create();
        \App\Models\Despacho::factory(20)->create();
        \App\Models\Pedido_producto::factory(30)->create();
        \App\Models\Cotizacion::factory(30)->create();
        \App\Models\Cotizacion_producto::factory(30)->create();

        // 1. Asegurar copia de imágenes por defecto de HomeView en el storage
        $storageCarruselDir = storage_path('app/public/carruseles');
        if (!file_exists($storageCarruselDir)) {
            @mkdir($storageCarruselDir, 0775, true);
        }

        $frontendAssetsDir = base_path('../frontend-dicreme/src/assets');
        $imagenesAssets = ['banner1.webp', 'banner2.webp', 'local_horario.webp'];
        foreach ($imagenesAssets as $imgName) {
            $origin = $frontendAssetsDir . '/' . $imgName;
            $dest = $storageCarruselDir . '/' . $imgName;
            if (file_exists($origin) && !file_exists($dest)) {
                @copy($origin, $dest);
            }
        }

        // 2. Sembrar imágenes iniciales del carrusel en la BD
        $carruselesIniciales = [
            [
                'titulo' => 'Banner Principal Di Creme',
                'descripcion' => 'Helados artesanales de la más alta calidad',
                'imagen_url' => '/storage/carruseles/banner1.webp',
                'orden' => 1,
                'estado' => true
            ],
            [
                'titulo' => 'Promoción Distribuidores',
                'descripcion' => 'Descuentos especiales por compras al por mayor',
                'imagen_url' => '/storage/carruseles/banner2.webp',
                'orden' => 2,
                'estado' => true
            ],
            [
                'titulo' => 'Horario de Atención',
                'descripcion' => 'Atención presencial y despachos a toda la región',
                'imagen_url' => '/storage/carruseles/local_horario.webp',
                'orden' => 3,
                'estado' => true
            ]
        ];

        foreach ($carruselesIniciales as $c) {
            \App\Models\Carrusel::firstOrCreate(
                ['imagen_url' => $c['imagen_url']],
                $c
            );
        }

        // 3. Sembrar barra de avisos inicial en la base de datos (tabla avisos)
        $avisosIniciales = [
            "📢 Aviso: Horario de atención hasta las 17:00 hrs.",
            "🚛 Envíos gratuitos a toda la Región Metropolitana por compras sobre $50.000.",
            "🍦 Descuentos especiales para distribuidores registrados."
        ];

        foreach ($avisosIniciales as $index => $msg) {
            \App\Models\Aviso::firstOrCreate(
                ['mensaje' => $msg],
                [
                    'orden' => $index + 1,
                    'estado' => true
                ]
            );
        }
    }
}



