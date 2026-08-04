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
            'precio_formato' => 'required|integer|min:0',
            'imagen_formato' => 'nullable|string',
        ]);

        try {
            $formato = $this->formatoServices->createFormato($data);

            \App\Models\HistorialMovimiento::registrar(
                'producto',
                $formato->id,
                'creacion_formato',
                "Se creó el formato '{$formato->nombre_formato}' con precio base $" . number_format($formato->precio_formato, 0, ',', '.'),
                null
            );

            return response()->json([
                'status' => 'success', 
                'data' =>  $formato,
                'message' => "Formato creado correctamente"
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el formato: ' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id):JsonResponse
    {
        $data = $request->validate([
            'nombre_formato' => 'sometimes|required|string|max:255',
            'precio_formato' => 'sometimes|required|integer|min:0',
            'imagen_formato' => 'nullable|string',
        ]);

        try {
            $formato_update = $this->formatoServices->updateFormato($id, $data);

            if (isset($data['precio_formato']) && $formato_update) {
                \App\Models\Producto::where('id_formato', $id)->update(['precio_producto' => $data['precio_formato']]);
                \Illuminate\Support\Facades\Cache::forget(\App\Repositories\ProductoRepository::CACHE_KEY);
            }

            \App\Models\HistorialMovimiento::registrar(
                'producto',
                $formato_update->id,
                'modificacion_formato',
                "Se actualizó el formato '{$formato_update->nombre_formato}' con precio base $" . number_format($formato_update->precio_formato, 0, ',', '.'),
                null
            );

            return response()->json([
                'status' => 'success', 
                'data' =>  $formato_update,
                'message' => "Formato actualizado correctamente"
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el formato: ' . $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {   
        try {
            $formato = $this->formatoServices->getFormatoById($id);
            $formato_delete = $this->formatoServices->deleteFormato($id);

            if ($formato) {
                \App\Models\HistorialMovimiento::registrar(
                    'producto',
                    $id,
                    'eliminacion_formato',
                    "Se eliminó el formato '{$formato->nombre_formato}'",
                    null
                );
            }

            return response()->json([
                'status' => 'success', 
                'data' =>  $formato_delete,
                'message' => "Formato eliminado correctamente"
            ], 200); 
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el formato: ' . $e->getMessage()
            ], 400);
        }
    }
}