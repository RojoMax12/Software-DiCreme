<?php

namespace App\Http\Controllers;
use App\Services\DespachoServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Estado_pedidoController extends Controller
{
    protected $despachoServices;

    public function __construct(DespachoServices $despachoServices)
    {
        $this->despachoServices = $despachoServices;
    }

    
    public function store(Request $request):JsonResponse
    {   $data = $request->validate([
            'nombre_estado' => 'required|string|max:255',
        ]);

        try {
            $despacho = $this->despachoServices->createDespacho($data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $despacho, 
            'message' => "Despacho correctamente creado"],
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el despacho' . $e->getMessage()
            ], 400);
        }
        
        
    }

    public function update(Request $request, $id):JsonResponse
    {
        $data = $request->validate([
            'nombre_estado' => 'required|string|max:255',
        ]);

        try {
            $despacho_update = $this->despachoServices->updateDespacho($id, $data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $despacho_update,
            'message' => "Despacho  actualizado correctamente"], 
            200); 
        } catch (\Exception $e) {
           return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el despacho' . $e->getMessage()
            ], 400);
        }
        
    }

    public function destroy($id):JsonResponse
    {   
        try {
            $despacho_delete = $this->despachoServices->deleteDespacho($id);

            return response()->json([
            'status' => 'success', 
            'data' =>  $despacho_delete,
            'message' => "Despacho  eliminado correctamente"], 
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el despacho' . $e->getMessage()
            ], 400);
        }
    }


    public function index()
    {
        return response()->json($this->despachoServices->getAllDespachos());
    }

    public function show($id)
    {
        return response()->json($this->despachoServices->getDespachoById($id));
    }
}