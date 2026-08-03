<?php

namespace App\Http\Controllers;

use App\Services\Estado_cotizacionServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class Estado_cotizacionController extends Controller
{
    protected $estadoCotizacionServices;

    public function __construct(Estado_cotizacionServices $estadoCotizacionServices)
    {
        $this->estadoCotizacionServices = $estadoCotizacionServices;
    }

    public function index(): JsonResponse
    {
        try {
            return response()->json([
            'status' => 'success', 
            'data' => $this->estadoCotizacionServices->getAllEstadosCotizacion()], 
            200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error al listar estados de cotización', $e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['nombre_estado' => 'required|string|max:255']);
        try {
            return response()->json(['status' => 'success', 'data' => $this->estadoCotizacionServices->createEstadoCotizacion($data)], 201);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error al crear estado', $e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate(['nombre_estado' => 'required|string|max:255']);
        try {
            return response()->json(['status' => 'success', 'data' => $this->estadoCotizacionServices->updateEstadoCotizacion($id, $data)], 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error al actualizar estado', $e);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->estadoCotizacionServices->deleteEstadoCotizacion($id);
            return response()->json(['status' => 'success', 'message' => 'Estado eliminado'], 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error al eliminar estado', $e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return response()->json(['status' => 'success', 'data' => $this->estadoCotizacionServices->getEstadoCotizacionById($id)], 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error al obtener estado', $e);
        }
    }

    private function errorResponse(string $message, \Throwable $e): JsonResponse {
        \Illuminate\Support\Facades\Log::error($message . ': ' . $e->getMessage(), ['exception' => $e]);
        return response()->json([
            'status'  => 'error',
            'message' => $message . ($e->getMessage() ? ': ' . $e->getMessage() : ''),
            'error'   => $e->getMessage()
        ], 500);
    }
}