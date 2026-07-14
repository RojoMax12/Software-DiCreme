<?php

namespace App\Http\Controllers;

use App\Services\BodegaServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BodegaController extends Controller
{
    protected $bodegaServices;

    public function __construct(BodegaServices $bodegaServices)
    {
        $this->bodegaServices = $bodegaServices;
    }


    public function store(Request $request):JsonResponse
    {
        $data = $request->validate([
            'nombre_bodega' => 'required|string|max:255',
            'ubicacion_bodega' => 'required|string|max:255',
            'cantidad_productos' => 'required|integer|min:0',
        ]);

        try{
            $bodega_creada = $this->bodegaServices->createBodega($data);

            return response()->json([
                'status' => 'success',
                'data' => $bodega_creada,
                'message' => 'bodega creada correctamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar al crear la bodega ' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id):JsonResponse
    {
        $data = $request->validate([
            'nombre_bodega' => 'sometimes|required|string|max:255',
            'ubicacion_bodega' => 'sometimes|required|string|max:255',
            'cantidad_productos' => 'sometimes|required|integer|min:0',
        ]);

        try {
            $bodega_updateada =  $this->bodegaServices->updateBodega($id, $data);

            return response()->json([
                'status' => 'success',
                'data' => $bodega_updateada,
                'message' => 'bodega actualizada correctamente'
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar la bodega' . $e->getMessage()
            ], 400);
  
        }

        
    }

    public function destroy($id):JsonResponse
    {   
        try {
            
            $bodega_destruida = $this->bodegaServices->deleteBodegaById($id);

            return response()->json([
                'status' => 'success',
                'data' => $bodega_destruida,
                'message' => 'bodega eliminada correctamente'
            ], 201);

        } catch (\Exception $e) {

        return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar la bodega' . $e->getMessage()
            ], 400);

        }
  
    }


    public function index():JsonResponse
    {   
        try {

            $bodegas = $this->bodegaServices->getAllBodegas();
            return response()->json([
            'status' => 'success', 
            'data' =>  $bodegas], 
            200); 

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las bodegas' . $e->getMessage()
            ], 400);
        }
       
    }

    public function show($id):JsonResponse
    {   
        try {

            $bodega = $this->bodegaServices->getBodegaById($id);
            return response()->json([
            'status' => 'success', 
            'data' =>  $bodega], 
            200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las bodega' . $e->getMessage()
            ], 400);
        }
    }
}