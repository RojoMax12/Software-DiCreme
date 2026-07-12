<?php

namespace App\Http\Controllers;
use App\Services\Estado_pagoServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Estado_pagoController extends Controller
{
    protected $Estado_pagoServices;

    public function __construct(Estado_pagoServices $Estado_pagoServices)
    {
        $this->Estado_pagoServices = $Estado_pagoServices;
    }

    
    public function store(Request $request):JsonResponse
    {   $data = $request->validate([
            'nombre_estado' => 'required|string|max:255',
        ]);

        try {
            $Estado_pedido = $this->Estado_pagoServices->createEstadoPago($data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $Estado_pedido, 
            'message' => "Estado de pago orrectamente creado"],
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
            $pedido_update = $this->Estado_pagoServices->updateEstadoPago($id, $data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $pedido_update,
            'message' => "Estado de pago actualizado correctamente"], 
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
            $pedido_delete = $this->Estado_pagoServices->deleteEstadoPago($id);

            return response()->json([
            'status' => 'success', 
            'data' =>  $pedido_delete,
            'message' => "Estado de pago liminado correctamente"], 
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
        return response()->json($this->Estado_pagoServices->getAllEstadosPago());
    }

    public function show($id)
    {
        return response()->json($this->Estado_pagoServices->getEstadoPagoById($id));
    }
}