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
use App\Http\Controllers\Estado_cotizacionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle:auth_limits')->group(function () {

	Route::post('/login', [AuthController::class, 'login']);

	Route::middleware('jwt.auth')->group(function () {
		Route::get('/me', [AuthController::class, 'me']);
		Route::post('/refresh', [AuthController::class, 'refresh']);
		Route::post('/logout', [AuthController::class, 'logout']);
	});
});


Route::middleware('throttle:api_lectura')->group(function () {

	Route::get('/cotizaciones', [CotizacionController::class, 'index']);
	Route::get('/cotizaciones/{id}', [CotizacionController::class, 'show']);

	Route::get('/cotizacion_producto', [Cotizacion_productoController::class, 'index']);
	Route::get('/cotizacion_producto/{id}', [Cotizacion_productoController::class, 'show']);

		/* Rutas para el controlador de categorias */
	Route::get('/categorias', [CategoriaController::class, 'index']);
	Route::get('/categorias/{id}', [CategoriaController::class, 'show']);
	

	/* Rutas para el controlador de formatos */
	Route::get('/formatos', [FormatoController::class, 'index']);
	Route::get('/formatos/{id}', [FormatoController::class, 'show']);


	/* Rutas para el controlador de productos */
	Route::get('/productos', [ProductoController::class, 'index']);
	Route::get('/productos/{id}', [ProductoController::class, 'show']);

	Route::get('/cotizacion_producto/cotizacion/{idCotizacion}', [Cotizacion_productoController::class, 'getByCotizacionId']);
	Route::get('/cotizacion_producto/producto/{idProducto}', [Cotizacion_productoController::class, 'getByProductoId']);

});

Route::middleware('throttle:api_escritura')->group(function () {

	Route::post('/cotizaciones', [CotizacionController::class, 'store'])->middleware('role: 1, 2 ,3 ');
	Route::put('/cotizaciones/{id}', [CotizacionController::class, 'update'])->middleware('role: 1, 2 ,3 ');
	Route::delete('/cotizaciones/{id}', [CotizacionController::class, 'destroy'])->middleware('role: 1');
	Route::delete('/cotizacion_producto/{id}', [Cotizacion_productoController::class, 'destroy'])->middleware('role: 1');

		/* Rutas para el controlador de usuarios dicreme y distribuidores */
	Route::post('/usuarios_dicreme', [Usuario_dicremeController::class, 'store'])->middleware('role: 1'); 
	Route::post('/usuarios_distribuidores', [Usuario_distribuidoresController::class, 'store']);
});

		Route::middleware('jwt.auth')->group(function () {

			Route::middleware('throttle:api_escritura')->group(function () {


			Route::post('/categorias', [CategoriaController::class, 'store'])->middleware('role: 1');
			Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->middleware('role: 1');
			Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->middleware('role: 1');

			/* Rutas para el controlador de formatos */
			Route::post('/formatos', [FormatoController::class, 'store'])->middleware('role: 1');
			Route::put('/formatos/{id}', [FormatoController::class, 'update'])->middleware('role: 1');
			Route::delete('/formatos/{id}', [FormatoController::class, 'destroy'])->middleware('role: 1');

			/* Rutas para el controlador de productos */
			Route::post('/productos', [ProductoController::class, 'store'])->middleware('role: 1');
			Route::put('/productos/{id}', [ProductoController::class, 'update'])->middleware('role: 1');
			Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->middleware('role: 1');


			Route::post('/roles', [RolController::class, 'store'])->middleware('role: 1');;         // Crear
			Route::put('/roles/{id}', [RolController::class, 'update'])->middleware('role: 1');;    // Editar
			Route::delete('/roles/{id}', [RolController::class, 'destroy'])->middleware('role: 1'); // Eliminar/

			Route::put('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'update'])->middleware('role: 1');
			Route::delete('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'destroy'])->middleware('role: 1');


			Route::put('/usuarios_dicreme/{id}', [Usuario_dicremeController::class, 'update'])->middleware('role: 1');
			Route::delete('/usuarios_dicreme/{id}', [Usuario_dicremeController::class, 'destroy'])->middleware('role: 1');

			Route::post('/pedidos', [PedidoController::class, 'store']);
			Route::put('/pedidos/{id}', [PedidoController::class, 'update'])->middleware('role: 1, 2');
			Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy'])->middleware('role: 1');

			Route::post('/estado_pedido', [Estado_pedidoController::class, 'store'])->middleware('role: 1');
			Route::put('/estado_pedido/{id}', [Estado_pedidoController::class, 'update'])->middleware('role: 1');
			Route::delete('/estado_pedido/{id}', [Estado_pedidoController::class, 'destroy'])->middleware('role: 1');

			Route::post('/despachos', [DespachoController::class, 'store']);
			Route::put('/despachos/{id}', [DespachoController::class, 'update'])->middleware('role: 1');
			Route::delete('/despachos/{id}', [DespachoController::class, 'destroy'])->middleware('role: 1');

			Route::post('/bodegas', [BodegaController::class, 'store'])->middleware('role: 1');
			Route::put('/bodegas/{id}', [BodegaController::class, 'update'])->middleware('role: 1');
			Route::delete('/bodegas/{id}', [BodegaController::class, 'destroy'])->middleware('role: 1');

			Route::post('/lotes', [LoteController::class, 'store']);
			Route::put('/lotes/{id}', [LoteController::class, 'update'])->middleware('role: 1');
			Route::delete('/lotes/{id}', [LoteController::class, 'destroy'])->middleware('role: 1');

			Route::post('/pedido_producto', [Pedido_productoController::class, 'store']);
			Route::put('/pedido_producto/{id}', [Pedido_productoController::class, 'update'])->middleware('role: 1');
			Route::delete('/pedido_producto/{id}', [Pedido_productoController::class, 'destroy'])->middleware('role: 1');

			Route::post('/stocks', [StockController::class, 'store']);
			Route::put('/stocks/{id}', [StockController::class, 'update'])->middleware('role: 1');
			Route::delete('/stocks/{id}', [StockController::class, 'destroy'])->middleware('role: 1');

			Route::post('/ventas', [VentaController::class, 'store']);
			Route::put('/ventas/{id}', [VentaController::class, 'update'])->middleware('role: 1');
			Route::delete('/ventas/{id}', [VentaController::class, 'destroy'])->middleware('role: 1');

			Route::post('/cotizacion/{id}/transformar', [CotizacionController::class, 'transformarCotizacionEnPedido'])->middleware('role: 1');
			Route::get('/cotizaciones/{id}/usuario_dicreme',[CotizacionController::class, 'getallCotizacionesByUsuariodicreme'])->middleware('role: 1');

			Route::get('/pedidos/{id}/usuario_dicreme', [PedidoController::class, 'getallPedidosByUsuariodicreme'])->middleware('role: 1');


			Route::post('/estado_cotizacion', [Estado_cotizacionController::class, 'store'])->middleware('role:1');
			Route::put('/estado_cotizacion/{id}', [Estado_cotizacionController::class, 'update'])->middleware('role:1');
			Route::delete('/estado_cotizacion/{id}', [Estado_cotizacionController::class, 'destroy'])->middleware('role:1');

		});


		Route::middleware('throttle:api_lectura')->group(function () {

			Route::get('/estado_cotizacion', [Estado_cotizacionController::class, 'index'])->middleware('role:1');
			Route::get('/estado_cotizacion/{id}', [Estado_cotizacionController::class, 'show'])->middleware('role:1');


			Route::get('/roles', [RolController::class, 'index']);        // Listar, Index es el nombre de la función que esta en el controlador   
			Route::get('/roles/{id}', [RolController::class, 'show']);      // Ver uno
			

			/*Rutas para el controlador de usuarios distribuidores */
			Route::get('/usuarios_distribuidores', [Usuario_distribuidoresController::class, 'index']);      
			Route::get('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'show']);             
			

			/* Rutas para el controlador de usuarios dicreme */
			Route::get('/usuarios_dicreme', [Usuario_dicremeController::class, 'index']);          
			Route::get('/usuarios_dicreme/{id}', [Usuario_dicremeController::class, 'show']);        


			/* Rutas para el controlador de pedidos */
			Route::get('/pedidos', [PedidoController::class, 'index']);
			Route::get('/pedidos/{id}', [PedidoController::class, 'show']);
			

			/* Rutas para el controlador de estado_pedido */
			Route::get('/estado_pedido', [Estado_pedidoController::class, 'index']);
			Route::get('/estado_pedido/{id}', [Estado_pedidoController::class, 'show']);
			

			/* Rutas para el controlador de despachos */
			Route::get('/despachos', [DespachoController::class, 'index']);
			Route::get('/despachos/{id}', [DespachoController::class, 'show']);
			

			/* Rutas para el controlador de bodegas */
			Route::get('/bodegas', [BodegaController::class, 'index']);
			Route::get('/bodegas/{id}', [BodegaController::class, 'show']);
			

			/* Rutas para el controlador de lotes */
			Route::get('/lotes', [LoteController::class, 'index']);
			Route::get('/lotes/{id}', [LoteController::class, 'show']);
			

			/* Rutas para el controlador de pedido_producto */
			Route::get('/pedido_producto', [Pedido_productoController::class, 'index']);
			Route::get('/pedido_producto/{id}', [Pedido_productoController::class, 'show']);
			

			/* Rutas para el controlador de stocks */
			Route::get('/stocks', [StockController::class, 'index']);
			Route::get('/stocks/{id}', [StockController::class, 'show']);
			

			/* Rutas para el controlador de ventas */
			Route::get('/ventas', [VentaController::class, 'index']);
			Route::get('/ventas/{id}', [VentaController::class, 'show']);

		});
		
		
});



