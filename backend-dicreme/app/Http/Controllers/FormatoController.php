<?php

namespace App\Http\Controllers;

use App\Services\FormatoServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormatoController extends Controller
{
    protected $formatoServices;

    public function __construct(FormatoServices $formatoServices)
    {
        $this->formatoServices = $formatoServices;
    }

    public function index()
    {
        return response()->json($this->formatoServices->getAllFormatos());
    }

    public function show($id)
    {
        return response()->json($this->formatoServices->getFormatoById($id));
    }

    public function store(Request $request):JsonResponse
    {
        $data = $request->validate([
            'nombre_formato' => 'required|string|max:255',
        ]);

        try {
            $formato = $this->formatoServices->createFormato($data);

            return response()->json([
            'status' => 'success', 
            'data' =>  $formato,
            'message' => "Formato creado correctamente"], 
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el formato' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id):JsonResponse
    {
        $data = $request->validate([
            'nombre_formato' => 'sometimes|required|string|max:255',
        ]);

        try {

        $formato_update = $this->formatoServices->updateFormato($id, $data);

        return response()->json([
            'status' => 'success', 
            'data' =>  $formato_update,
            'message' => "Formato actualizado correctamente"], 
            200); 

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el formato' . $e->getMessage()
            ], 400);
        }

    }

    public function destroy($id)
    {   
        try {
            $formato_delete = $this->formatoServices->deleteFormato($id);

            return response()->json([
            'status' => 'success', 
            'data' =>  $formato_delete,
            'message' => "Formato eliminado correctamente"], 
            200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el formato' . $e->getMessage()
            ], 400);
        }
    }
}