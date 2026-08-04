<?php

namespace App\Http\Controllers;
use App\Services\PedidoServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    protected $pedidoService;

    public function __construct(PedidoServices $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    public function store(Request $request):JsonResponse
    {
        $data = $request->validate([
            'id_cotizacion' => 'required|integer|exists:cotizaciones,id',
            'id_usuario_distribuidor' =>'required|integer|exists:usuario_distribuidores,id',
            'fecha_creacion' => 'required|date',
            'id_estado_pedido' => 'required|integer|exists:estados_pedido,id',
            'id_usuario_dicreme' => 'sometimes|integer|exists:usuarios_dicreme,id',
            'monto_estimado' => 'required|integer',
            'monto_final' => 'required|integer',
        ]);

        try {
            $pedido_creado = $this->pedidoService->createPedido($data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $pedido_creado,
            'message' =>"Pedido creado exitosamente"], 
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
            'id_cotizacion' => 'sometimes|required|integer|exists:cotizaciones,id',
            'id_usuario_distribuidor' =>'required|integer|exists:usuario_distribuidores,id',
            'fecha_creacion' => 'sometimes|required|date',
            'id_estado_pedido' => 'sometimes|required|integer|exists:estados_pedido,id',
            'id_usuario_dicreme' => 'sometimes|integer|exists:usuarios_dicreme,id',
            'monto_estimado' => 'sometimes|required|integer',
            'monto_final' => 'sometimes|required|integer',
        ]);

        try {
            $pedido_actualizado = $this->pedidoService->updatePedido($id, $data);
            return response()->json([
            'status' => 'success', 
            'data' =>  $pedido_actualizado,
            'message' => "Pedido actualizado correctamente"], 
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el pedido' . $e->getMessage()
            ], 400);
        }


    }

    public function destroy($id):JsonResponse
    {   
        try {
            $pedido_eliminado = $this->pedidoService->deletePedido($id);

            return response()->json([
            'status' => 'success', 
            'data' =>  $pedido_eliminado,
            'message' => "Pedido eliminado correctamente"], 
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el pedido' . $e->getMessage()
            ], 400);
        }

    }


        public function index()
    {
        return response()->json($this->pedidoService->getAllPedidos());
    }

    public function show($id)
    {
        return response()->json($this->pedidoService->getPedidoById($id));
    }


    public function getallPedidosByUsuariodicreme($id_usuario_dicreme)
    {
        return response() ->json($this->pedidoService->getPedidoByUsuario($id_usuario_dicreme));
    }

    public function getallPedidosByUsuariodistribuidor($id_usuario_distribuidor)
    {
        return response() ->json($this->pedidoService->getPedidoByUsuario_distribuidores($id_usuario_distribuidor));
    }

    public function cambiarEstado(Request $request, $id_pedido)
    {
        try {
            $id_estado = $request->input('id_estado_pedido') ?? $request->input('id_estado');
            
            // Ejecutamos la función secuencial interna
            $pedidoActualizado = $this->pedidoService->actualizarEstadoPedido($id_pedido, $id_estado);

            return response()->json([
                'status'  => 'success',
                'message' => "El estado del pedido #{$id_pedido} se actualizó correctamente.",
                'data'    => $pedidoActualizado
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function cambiarEstadoPago(Request $request, $id_pago)
    {
        try {
            $id_estado = $request->input('id_estado_pago');
            
            // Ejecutamos la función secuencial interna
            $pedidoActualizado = $this->pedidoService->actualizarEstadoPago($id_pago, $id_estado);

            return response()->json([
                'status'  => 'success',
                'message' => "El estado del pago se actualizó correctamente.",
                'data'    => $pedidoActualizado
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getdetailpedido($id){
        
        $resultado = $this->pedidoService->getDetailPedido($id);

        if( $resultado === false){
            return response()->json([
                'status' => 'error',
                'message' => 'No existe el pedido'
            ], 403); 
        
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detalles de la cotizacion obtenidas exitosamente',
            'data' => $resultado
        ], 200); // 200 OK
    }
}