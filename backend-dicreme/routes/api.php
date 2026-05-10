<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\Usuario_dicremeController;
use App\Http\Controllers\Usuario_distribuidoresController;
use App\Http\Controllers\DespachoController;
use App\Http\Controllers\Estado_pedidoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
	Route::post('/login', [AuthController::class, 'login']);

	Route::middleware('jwt.auth')->group(function () {
		Route::get('/me', [AuthController::class, 'me']);
		Route::post('/refresh', [AuthController::class, 'refresh']);
		Route::post('/logout', [AuthController::class, 'logout']);
	});
});

Route::middleware('jwt.auth')->group(function () {
	/* Rutas para el controlador de roles */
	Route::get('/roles', [RolController::class, 'index']);          // Listar, Index es el nombre de la función que esta en el controlador   
	Route::get('/roles/{id}', [RolController::class, 'show']);      // Ver uno
	Route::post('/roles', [RolController::class, 'store']);         // Crear
	Route::put('/roles/{id}', [RolController::class, 'update']);    // Editar
	Route::delete('/roles/{id}', [RolController::class, 'destroy']); // Eliminar/

	/*Rutas para el controlador de usuarios distribuidores */
	Route::get('/usuarios_distribuidores', [Usuario_distribuidoresController::class, 'index']);      
	Route::get('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'show']);      
	Route::post('/usuarios_distribuidores', [Usuario_distribuidoresController::class, 'store']);        
	Route::put('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'update']);    
	Route::delete('/usuarios_distribuidores/{id}', [Usuario_distribuidoresController::class, 'destroy']); 

	/* Rutas para el controlador de usuarios dicreme */
	Route::get('/usuarios_dicreme', [Usuario_dicremeController::class, 'index']);          
	Route::get('/usuarios_dicreme/{id}', [Usuario_dicremeController::class, 'show']);      
	Route::post('/usuarios_dicreme', [Usuario_dicremeController::class, 'store']);        
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

	/* Rutas para el controlador de ventas */
	Route::get('/ventas', [VentaController::class, 'index']);
	Route::get('/ventas/{id}', [VentaController::class, 'show']);
	Route::post('/ventas', [VentaController::class, 'store']);
	Route::put('/ventas/{id}', [VentaController::class, 'update']);
	Route::delete('/ventas/{id}', [VentaController::class, 'destroy']);
});


