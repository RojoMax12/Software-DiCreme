<?php

namespace App\Http\Controllers;

use App\Services\DespachoServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class DespachoController extends Controller
{
    protected $despachoServices;

    public function __construct(DespachoServices $despachoServices)
    {
        $this->despachoServices = $despachoServices;
    }

    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data'   => $this->despachoServices->getAllDespachos()
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al listar despachos', $e);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $despacho = $this->despachoServices->getDespachoById($id);
            if (!$despacho) {
                return response()->json(['status' => 'error', 'message' => 'Despacho no encontrado'], 404);
            }
            return response()->json(['status' => 'success', 'data' => $despacho], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener despacho', $e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_pedido'          => 'required|integer|exists:pedidos,id',
            'direccion_entrega'  => 'required|string|max:255',
            'fecha_entrega'      => 'required|date',
            'persona_recibe'     => 'required|string|max:255',
            'comuna'             => 'required|string|max:255',
            'estado_despacho'    => 'required|string|max:40',
            'id_usuario_dicreme' => 'required|integer|exists:usuario_dicreme,id'
        ]);

        try {
            return response()->json([
                'status' => 'success',
                'data'   => $this->despachoServices->createDespacho($data),
                'message' => 'Despacho creado correctamente'
            ], 201);
        } catch (Exception $e) {
            return $this->errorResponse('Error al crear despacho', $e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'id_pedido'          => 'required|integer|exists:pedidos,id',
            'direccion_entrega'  => 'required|string|max:255',
            'fecha_entrega'      => 'required|date',
            'persona_recibe'     => 'required|string|max:255',
            'comuna'             => 'required|string|max:255',
            'estado_despacho'    => 'required|string|max:40',
            'id_usuario_dicreme' => 'required|integer|exists:usuario_dicreme,id'
        ]);

        try {
            return response()->json([
                'status' => 'success',
                'data'   => $this->despachoServices->updateDespacho($id, $data)
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al actualizar despacho', $e);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->despachoServices->deleteDespacho($id);
            return response()->json(['status' => 'success', 'message' => 'Despacho eliminado'], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al eliminar despacho', $e);
        }
    }

    public function getdespachobyidpedido($id): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data'   => $this->despachoServices->despachosbyidpedido($id)
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al buscar despacho por pedido', $e);
        }
    }

    public function getdespachobyidusuariodicreme($id): JsonResponse
    {
        try {
            $despachos = $this->despachoServices->getDespachoByIdusuariodicreme($id);

            if ($despachos === null) {
                return response()->json(['status' => 'error', 'message' => 'No existen despachos asignados'], 404);
            }
            if ($despachos === false) {
                return response()->json(['status' => 'error', 'message' => 'El usuario no es un despachador válido'], 400);
            }

            return response()->json(['status' => 'success', 'data' => $despachos], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al consultar despachos del despachador', $e);
        }
    }

    public function getdespachosdisponibles(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data'   => $this->despachoServices->getDespachosDisponibles()
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al listar despachos disponibles', $e);
        }
    }

    public function asignardespachoadespachador($id_despacho, $id_despachador): JsonResponse
    {
        try {
            $despacho = $this->despachoServices->asignardespachoaldespachador($id_despacho, $id_despachador);

            if ($despacho === null) {
                return response()->json(['status' => 'error', 'message' => 'El despacho no existe'], 404);
            }
            if ($despacho === false) {
                return response()->json(['status' => 'error', 'message' => 'Error de asignación: usuario no es despachador'], 400);
            }

            return response()->json(['status' => 'success', 'message' => 'Asignado correctamente', 'data' => $despacho], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error en la asignación', $e);
        }
    }

    public function iniciarRuta($id_despacho): JsonResponse
    {
        try {
            $despacho = $this->despachoServices->iniciarRuta($id_despacho);
            if (!$despacho) {
                return response()->json(['status' => 'error', 'message' => 'El despacho no existe'], 404);
            }
            return response()->json(['status' => 'success', 'message' => 'Ruta iniciada correctamente', 'data' => $despacho], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al iniciar ruta', $e);
        }
    }

    public function finalizarEntrega(Request $request, $id_despacho): JsonResponse
    {
        try {
            $request->validate([
                'foto_comprobante' => 'nullable|file|image|max:10240',
                'notas_entrega'    => 'nullable|string'
            ]);

            $fotoFile = $request->file('foto_comprobante');
            $notas = $request->input('notas_entrega');

            $despacho = $this->despachoServices->finalizarEntrega($id_despacho, $notas, $fotoFile);
            if (!$despacho) {
                return response()->json(['status' => 'error', 'message' => 'El despacho no existe'], 404);
            }
            return response()->json(['status' => 'success', 'message' => 'Entrega finalizada con éxito', 'data' => $despacho], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al finalizar entrega', $e);
        }
    }

    public function liberarDespacho($id_despacho): JsonResponse
    {
        try {
            $res = $this->despachoServices->liberarDespacho($id_despacho);
            if ($res['status'] === 'error') {
                return response()->json(['status' => 'error', 'message' => $res['message']], $res['code'] ?? 400);
            }

            return response()->json(['status' => 'success', 'message' => 'Despacho liberado correctamente', 'data' => $res['data']], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al liberar el despacho', $e);
        }
    }

    private function errorResponse(string $message, Exception $e): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'debug'   => config('app.debug') ? $e->getMessage() : 'Error interno del servidor'
        ], 500);
    }

    public function enviarCorreoDistribuidor($id_despacho): JsonResponse
    {
        try {
            $enviado = $this->despachoServices->enviarNotificacionDespacho($id_despacho);

            if (!$enviado) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo enviar la notificación. Verifica que el despacho, el pedido asociado y el distribuidor existan y tengan un correo válido.'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Correo enviado correctamente'
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al enviar correo', $e);
        }
    }
}