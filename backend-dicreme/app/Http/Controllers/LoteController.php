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
            
            \App\Models\HistorialMovimiento::registrar(
                'lote',
                $lote_creado->id,
                'creacion_lote',
                "Se creó el lote #{$lote_creado->id} con {$lote_creado->cantidad_producida} unidades",
                null
            );

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

            \App\Models\HistorialMovimiento::registrar(
                'lote',
                $id,
                'modificacion_lote',
                "Se actualizó el lote #{$id}",
                null
            );

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

            \App\Models\HistorialMovimiento::registrar(
                'lote',
                $id,
                'eliminacion_lote',
                "Se eliminó el lote #{$id}",
                null
            );

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

        $loteAnterior = $this->loteServices->getLoteById($id);
        $cantPrevio = $loteAnterior ? $loteAnterior->cantidad_producto : 0;

        $loteActualizado = $this->loteServices->updateCantidadProducto($id, $data['cantidad_producto']);

        \App\Models\HistorialMovimiento::registrar(
            'lote',
            $id,
            'actualizacion_stock_lote',
            "Se actualizó la cantidad del lote #{$id} de {$cantPrevio} a {$data['cantidad_producto']} unidades",
            null,
            ['cantidad_anterior' => $cantPrevio, 'cantidad_nueva' => $data['cantidad_producto']]
        );

        return response()->json([
            'status' => 'success',
            'data' => $loteActualizado,
            'message' => "Cantidad del lote #{$id} actualizada a {$data['cantidad_producto']} unidades"
        ]);
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

    public function getLotesPorVencer(Request $request): JsonResponse
    {
        $dias = $request->query('dias', 30);
        $lotes = $this->loteServices->getLotesPorVencer($dias);
        
        return response()->json([
            'status' => 'success',
            'data' => $lotes
        ], 200);
    }

    public function verificarDisponibilidadStock(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_producto' => 'required|integer|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        $resultado = $this->loteServices->verificarDisponibilidadStock($data['items']);

        return response()->json([
            'status' => 'success',
            'data' => $resultado
        ], 200);
    }
}