<?php

namespace App\Http\Controllers;
use App\Services\Estado_pedidoServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Estado_pedidoController extends Controller
{
    protected $Estado_pedidoServices;

    public function __construct(Estado_pedidoServices $Estado_pedidoServices)
    {
        $this->Estado_pedidoServices = $Estado_pedidoServices;
    }

    
    public function store(Request $request):JsonResponse
    {   $data = $request->validate([
            'nombre_estado' => 'required|string|max:255',
        ]);

        try {
            $Estado_pedido = $this->Estado_pedidoServices->createEstadoPedido($data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $Estado_pedido, 
            'message' => "Estado de pedido correctamente creado"],
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el pedido' . $e->getMessage()
            ], 400);
        }
        
        
    }

    public function update(Request $request, $id):JsonResponse
    {
        $data = $request->validate([
            'nombre_estado' => 'required|string|max:255',
        ]);

        try {
            $pedido_update = $this->Estado_pedidoServices->updateEstadoPedido($id, $data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $pedido_update,
            'message' => "Estado de pedido  actualizado correctamente"], 
            200); 
        } catch (\Exception $e) {
           return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el estado del pedido' . $e->getMessage()
            ], 400);
        }
        
    }

    public function destroy($id):JsonResponse
    {   
        try {
            $pedido_delete = $this->Estado_pedidoServices->deleteEstadoPedido($id);

            return response()->json([
            'status' => 'success', 
            'data' =>  $pedido_delete,
            'message' => "Estado de pedido eliminado correctamente"], 
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el estado pedido' . $e->getMessage()
            ], 400);
        }
    }


    public function index()
    {
        return response()->json($this->Estado_pedidoServices->getAllEstadosPedido());
    }

    public function show($id)
    {
        return response()->json($this->Estado_pedidoServices->getAllEstadosPedido($id));
    }
}