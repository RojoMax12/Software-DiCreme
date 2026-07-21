<?php

namespace App\Http\Controllers;

use App\Models\HistorialMovimiento;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistorialMovimientoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = HistorialMovimiento::query();

            // Filtro por tipo de entidad (lote, usuario, producto)
            if ($request->filled('tipo_entidad')) {
                $query->where('tipo_entidad', strtolower($request->tipo_entidad));
            }

            // Filtro por id_entidad específico
            if ($request->filled('id_entidad')) {
                $query->where('id_entidad', $request->id_entidad);
            }

            // Buscador por palabra clave
            if ($request->filled('search')) {
                $q = strtolower(trim($request->search));
                $query->where(function($b) use ($q) {
                    $b->whereRaw('LOWER(descripcion) LIKE ?', ["%{$q}%"])
                      ->orWhereRaw('LOWER(usuario_responsable) LIKE ?', ["%{$q}%"])
                      ->orWhereRaw('LOWER(accion) LIKE ?', ["%{$q}%"]);
                });
            }

            $movimientos = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $movimientos
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener historial de movimientos',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tipo_entidad'        => 'required|string',
            'id_entidad'          => 'nullable|integer',
            'accion'              => 'required|string',
            'descripcion'         => 'required|string',
            'usuario_responsable' => 'nullable|string',
            'detalles_json'       => 'nullable|array'
        ]);

        try {
            $movimiento = HistorialMovimiento::create($data);

            return response()->json([
                'status' => 'success',
                'data' => $movimiento,
                'message' => 'Movimiento registrado correctamente'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar movimiento'
            ], 500);
        }
    }
}
