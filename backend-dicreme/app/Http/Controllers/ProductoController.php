<?php

namespace App\Http\Controllers;

use App\Services\ProductoServices;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    protected $productoServices;

    public function __construct(ProductoServices $productoServices)
    {
        $this->productoServices = $productoServices;
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'id_categoria' => 'required|integer|exists:categorias,id',
            'id_formato' => 'required|integer|exists:formatos,id',
            'nombre_producto' => 'required|string|max:255',
            'precio_producto' => 'required|integer|min:0',
            'estado_producto' => 'required|boolean',
        ]);

        try {
            $producto_creado = $this->productoServices->createProducto($data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $producto_creado,
            'message', "Producto creado correctamente"], 
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el producto' . $e->getMessage()
            ], 400);
        }

       
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_categoria' => 'sometimes|required|integer|exists:categorias,id',
            'id_formato' => 'sometimes|required|integer|exists:formatos,id',
            'nombre_producto' => 'sometimes|required|string|max:255',
            'precio_producto' => 'sometimes|required|integer|min:0',
            'estado_producto' => 'sometimes|required|boolean',
        ]);

        try {
            $producto_actualizado = $this->productoServices->updateProducto($id, $data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $producto_actualizado,
            'message', "Producto creado correctamente"], 
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el producto' . $e->getMessage()
            ], 400);
        }

    }

    public function destroy($id)
    {   
        try {
            $producto_destroy = $this->productoServices->deleteProducto($id);

            return response()->json([
            'status' => 'success', 
            'data' =>  $producto_destroy,
            'message', "Producto creado correctamente"], 
            200); 
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el producto' . $e->getMessage()
            ], 400);
        }

    }




    public function index()
    {
        return response()->json($this->productoServices->getAllProductos());
    }

    public function show($id)
    {
        return response()->json($this->productoServices->getProductoById($id));
    }

    public function getCantidadTotal($id){
        return response()->json($this->productoServices->getCantidadTotalProductoFromAllLotes($id));
    }

    public function getResumenTodosLosProductos()
    {
        return response()->json($this->productoServices->getResumenTodosLosProductos());
    }

    public function toggleestadoproducto($name)
    {
        // Llamamos a la función y guardamos el modelo o null
        $resultado = $this->productoServices->activarydesactivar($name);

        // Si es null, significa que el producto no existía en la BD
        if (is_null($resultado)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo encontrar el producto.',
            ], 404);
        }

        // Si llegamos aquí, se actualizó correctamente
        return response()->json([
            'status' => 'success',
            'message' => 'Estado del producto cambiado correctamente.',
            'data' => [
                'nombre_producto' => $resultado->nombre_producto,
                'estado_producto' => $resultado->estado_producto // Mandamos el nuevo estado real (true o false)
            ]
        ], 200);
    }
}