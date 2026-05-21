<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\FormatoController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\Pedido_productoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\Usuario_dicremeController;
use App\Http\Controllers\Usuario_distribuidoresController;
use App\Http\Controllers\DespachoController;
use App\Http\Controllers\Estado_pedidoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\Cotizacion_productoController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
	Route::post('/login', [AuthController::class, 'login']);

	Route::middleware('jwt.auth')->group(function () {
		Route::get('/me', [AuthController::class, 'me']);
		Route::post('/refresh', [AuthController::class, 'refresh']);
		Route::post('/logout', [AuthController::class, 'logout']);
	});
});

	Route::get('/cotizaciones', [CotizacionController::class, 'index']);
	Route::get('/cotizaciones/{id}', [CotizacionController::class, 'show']);
	Route::post('/cotizaciones', [CotizacionController::class, 'store']);
	Route::put('/cotizaciones/{id}', [CotizacionController::class, 'update']);
	Route::delete('/cotizaciones/{id}', [CotizacionController::class, 'destroy']);

	Route::get('/cotizacion_producto', [Cotizacion_productoController::class, 'index']);
	Route::get('/cotizacion_producto/{id}', [Cotizacion_productoController::class, 'show']);
	Route::post('/cotizacion_producto', [Cotizacion_productoController::class, 'store']);
	Route::put('/cotizacion_producto/{id}', [Cotizacion_productoController::class, 'update']);
	Route::delete('/cotizacion_producto/{id}', [Cotizacion_productoController::class, 'destroy']);

	/* Rutas para el controlador de usuarios dicreme y distribuidores */
	Route::post('/usuarios_dicreme', [Usuario_dicremeController::class, 'store']);      
	Route::post('/usuarios_distribuidores', [Usuario_distribuidoresController::class, 'store']); 

	/* Rutas para el controlador de categorias */
	Route::get('/categorias', [CategoriaController::class, 'index']);
	Route::get('/categorias/{id}', [CategoriaController::class, 'show']);
	

	/* Rutas para el controlador de formatos */
	Route::get('/formatos', [FormatoController::class, 'index']);
	Route::get('/formatos/{id}', [FormatoController::class, 'show']);


	/* Rutas para el controlador de productos */
	Route::get('/productos', [ProductoController::class, 'index']);
	Route::get('/productos/{id}', [ProductoController::class, 'show']);


Route::middleware('jwt.auth')->group(function () {


	/* Rutas para el controlador de categorias */
	Route::post('/categorias', [CategoriaController::class, 'store']);
	Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
	Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

	/* Rutas para el controlador de formatos */
	Route::post('/formatos', [FormatoController::class, 'store']);
	Route::put('/formatos/{id}', [FormatoController::class, 'update']);
	Route::delete('/formatos/{id}', [FormatoController::class, 'destroy']);

	/* Rutas para el controlador de productos */
	Route::post('/productos', [ProductoController::class, 'store']);
	Route::put('/productos/{id}', [ProductoController::class, 'update']);
	Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);

	/* Rutas para el controlador de roles */
	Route::get('/roles', [RolController::class, 'index']);          // Listar, Index es el nombre de la función que esta en el controlador   
	Route::get('/roles/{id}', [RolController::class, 'show']);      // Ver uno
	Route::post('/roles', [RolController::class, 'store']);         // Crear
	Route::put('/roles/{id}', [RolController::class, 'update']);    // Editar
	Route::delete('/roles/{id}', [RolController::class, 'destroy']); // Eliminar/

	/*Rutas para el controlador de usuarios distribuidores */
	Route::get('/usuarios_distribuidores', [Usuario_distribuidoresController::class, 'index']);      
	Route::get('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'show']);             
	Route::put('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'update']);    
	Route::delete('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'destroy']); 

	/* Rutas para el controlador de usuarios dicreme */
	Route::get('/usuarios_dicreme', [Usuario_dicremeController::class, 'index']);          
	Route::get('/usuarios_dicreme/{id}', [Usuario_dicremeController::class, 'show']);        
	Route::put('/usuarios_dicreme/{id}', [Usuario_dicremeController::class, 'update']);    
	Route::delete('/usuarios_dicreme/{id}', [Usuario_dicremeController::class, 'destroy']); 

	/* Rutas para el controlador de pedidos */
	Route::get('/pedidos', [PedidoController::class, 'index']);
	Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
	Route::post('/pedidos', [PedidoController::class, 'store']);
	Route::put('/pedidos/{id}', [PedidoController::class, 'update']);
	Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy']);   

	/* Rutas para el controlador de estado_pedido */
	Route::get('/estado_pedido', [Estado_pedidoController::class, 'index']);
	Route::get('/estado_pedido/{id}', [Estado_pedidoController::class, 'show']);
	Route::post('/estado_pedido', [Estado_pedidoController::class, 'store']);
	Route::put('/estado_pedido/{id}', [Estado_pedidoController::class, 'update']);
	Route::delete('/estado_pedido/{id}', [Estado_pedidoController::class, 'destroy']);

	/* Rutas para el controlador de despachos */
	Route::get('/despachos', [DespachoController::class, 'index']);
	Route::get('/despachos/{id}', [DespachoController::class, 'show']);
	Route::post('/despachos', [DespachoController::class, 'store']);
	Route::put('/despachos/{id}', [DespachoController::class, 'update']);
	Route::delete('/despachos/{id}', [DespachoController::class, 'destroy']);

	/* Rutas para el controlador de bodegas */
	Route::get('/bodegas', [BodegaController::class, 'index']);
	Route::get('/bodegas/{id}', [BodegaController::class, 'show']);
	Route::post('/bodegas', [BodegaController::class, 'store']);
	Route::put('/bodegas/{id}', [BodegaController::class, 'update']);
	Route::delete('/bodegas/{id}', [BodegaController::class, 'destroy']);

	
	/* Rutas para el controlador de lotes */
	Route::get('/lotes', [LoteController::class, 'index']);
	Route::get('/lotes/{id}', [LoteController::class, 'show']);
	Route::post('/lotes', [LoteController::class, 'store']);
	Route::put('/lotes/{id}', [LoteController::class, 'update']);
	Route::delete('/lotes/{id}', [LoteController::class, 'destroy']);

	/* Rutas para el controlador de pedido_producto */
	Route::get('/pedido_producto', [Pedido_productoController::class, 'index']);
	Route::get('/pedido_producto/{id}', [Pedido_productoController::class, 'show']);
	Route::post('/pedido_producto', [Pedido_productoController::class, 'store']);
	Route::put('/pedido_producto/{id}', [Pedido_productoController::class, 'update']);
	Route::delete('/pedido_producto/{id}', [Pedido_productoController::class, 'destroy']);


	/* Rutas para el controlador de stocks */
	Route::get('/stocks', [StockController::class, 'index']);
	Route::get('/stocks/{id}', [StockController::class, 'show']);
	Route::post('/stocks', [StockController::class, 'store']);
	Route::put('/stocks/{id}', [StockController::class, 'update']);
	Route::delete('/stocks/{id}', [StockController::class, 'destroy']);

	/* Rutas para el controlador de ventas */
	Route::get('/ventas', [VentaController::class, 'index']);
	Route::get('/ventas/{id}', [VentaController::class, 'show']);
	Route::post('/ventas', [VentaController::class, 'store']);
	Route::put('/ventas/{id}', [VentaController::class, 'update']);
	Route::delete('/ventas/{id}', [VentaController::class, 'destroy']);
});


