<?php

namespace App\Http\Controllers;

use App\Services\Pedido_productoServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Pedido_productoController extends Controller
{
    protected $pedidoProductoServices;

    public function __construct(Pedido_productoServices $pedidoProductoServices)
    {
        $this->pedidoProductoServices = $pedidoProductoServices;
    }

    public function store(Request $request):JsonResponse
    {
        $data = $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos,id',
            'id_producto' => 'required|integer|exists:productos,id',
            'precio_unitario_venta' => 'required|integer|min:0',
            'cantidad' => 'required|integer|min:1',
        ]);

        try {
            $Pedido_producto_create = $this->pedidoProductoServices->createPedidoProducto($data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $Pedido_producto_create,
            'message' => "Se a afiliado correctamente el producto al pedido"],
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo correctamente el producto al pedido' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id):JsonResponse
    {
        $data = $request->validate([
            'id_pedido' => 'sometimes|required|integer|exists:pedidos,id',
            'id_producto' => 'sometimes|required|integer|exists:productos,id',
            'precio_unitario_venta' => 'sometimes|required|integer|min:0',
            'cantidad' => 'sometimes|required|integer|min:1',
        ]);

        try {

            $Pedido_producto_update = $this->pedidoProductoServices->updatePedidoProducto($id, $data);
            return response()->json([
            'status' => 'success', 
            'data' =>  $Pedido_producto_update,
            'message' => "Se a actualizado correctamente el producto en el pedido"],
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo actualizar el producto en el pedido' . $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id):JsonResponse
    {   

        try {
            $Pedido_producto_destroy = $this->pedidoProductoServices->deletePedidoProducto($id);
            return response()->json([
            'status' => 'success', 
            'data' =>  $Pedido_producto_destroy,
            'message' => "Se a actualizado eliminado el producto en el pedido"],
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo eliminar el producto en el pedido' . $e->getMessage()
            ], 400);
        }
    }

    public function index()
    {
        return response()->json($this->pedidoProductoServices->getAllPedidoProductos());
    }

    public function show($id)
    {
        return response()->json($this->pedidoProductoServices->getPedidoProductoById($id));
    }

    
}