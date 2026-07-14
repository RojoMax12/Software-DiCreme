<?php

namespace App\Http\Controllers;

use App\Services\LoteServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    protected $loteServices;

    public function __construct(LoteServices $loteServices)
    {
        $this->loteServices = $loteServices;
    }


    public function store(Request $request):JsonResponse
    {
        $data = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id',
            'id_bodega' => 'required|integer|exists:bodegas,id',
            'cantidad_producida' => 'required|integer|min:0',
            'fecha_vencimiento' => 'required|date',
            'fecha_emision' => 'required|date',
        ]);

        try {
            $lote_creado = $this->loteServices->createLote($data);
            
            return response()->json([
            'status' => 'success', 
            'data' =>  $lote_creado,
            'message' => "Lote creado correctamente"], 
            200); 

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el lote' . $e->getMessage()
            ], 400);
        }

       
    }

    public function update(Request $request, $id):JsonResponse
    {
        $data = $request->validate([
            'id_producto' => 'sometimes|required|integer|exists:productos,id',
            'id_bodega' => 'sometimes|required|integer|exists:bodegas,id',
            'cantidad_producida' => 'required|integer|min:0',
            'cantidad_producto' => 'sometimes|required|integer|min:0',
            'fecha_vencimiento' => 'sometimes|required|date',
            'fecha_emision' => 'sometimes|required|date',
        ]);

        try {

            $lote_update = $this->loteServices->updateLote($id, $data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $lote_update,
            'message' => "Lote actualizado correctamente"], 
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el lote' . $e->getMessage()
            ], 400);
        }

    }

    public function destroy($id):JsonResponse
    {   
        try {

            $lote_destroy = $this->loteServices->deleteLote($id);

            return response()->json([
            'status' => 'success', 
            'data' =>  $lote_destroy,
            'message' => "Lote eliminado correctamente"], 
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el lote' . $e->getMessage()
            ], 400);
        }
        
    }

    public function updateCantidadProducto(Request $request, $id)
    {
        $data = $request->validate([
            'cantidad_producto' => 'required|integer|min:0',
        ]);

        return response()->json($this->loteServices->updateCantidadProducto($id, $data['cantidad_producto']));
    }

    public function getLotesByProductoId($id)
    {
        return response()->json($this->loteServices->getLotesByProductoId($id));
    }

     public function index()
    {
        return response()->json($this->loteServices->getAllLotes());
    }

    public function show($id)
    {
        return response()->json($this->loteServices->getLoteById($id));
    }

    public function getLoteMasReciente()
    {
        return response()->json($this->loteServices->getLoteMasReciente());
    }
}