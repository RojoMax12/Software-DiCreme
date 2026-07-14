<?php

namespace App\Http\Controllers;

use App\Services\CotizacionServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // Importación obligatoria
use Exception; // Para el manejo de errores

class CotizacionController extends Controller
{
    protected $cotizacionServices;
    protected $usuario_dicremeServices;

    public function __construct(CotizacionServices $cotizacionServices)
    {
        $this->cotizacionServices = $cotizacionServices;
    }

    public function index(): JsonResponse
    {
        try {
            $cotizaciones = $this->cotizacionServices->getAllCotizaciones();
            return response()->json([
                'status' => 'success',
                'data'   => $cotizaciones
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener las cotizaciones', $e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_distribuidor'      => 'required|integer|exists:usuarios_distribuidores,id',
            'id_usuario_dicreme'   => 'nullable|integer|exists:usuarios_dicreme,id',
            'id_estado_cotizacion' => 'required|integer|exists:estados_cotizacion,id',
            'total_cotizacion'     => 'required|numeric',
            'persona_recibe'       => 'required|string',
            'cotizacion_productos' => 'required|array|min:1',
            'cotizacion_productos.*.id_producto'           => 'required|integer|exists:productos,id',
            'cotizacion_productos.*.cantidad'              => 'required|integer|min:1',
            'cotizacion_productos.*.precio_unitario_venta' => 'required|numeric',
        ]);

        try {
            // Asignación de fechas del lado del servidor (seguridad)
            $data['fecha_creacion'] = now()->toDateString();
            $data['hora_creacion']  = now()->toTimeString();
            $data['subtotal_cotizacion'] = $data['total_cotizacion'];

            $cotizacion = $this->cotizacionServices->createCotizacion($data);

            return response()->json([
                'status'  => 'success',
                'message' => 'Cotización creada exitosamente',
                'data'    => $cotizacion
            ], 201);
        } catch (Exception $e) {
            return $this->errorResponse('Error al crear la cotización', $e);
        }
    }

    public function show($id): JsonResponse
    {   
        try {
            $cotizacion = $this->cotizacionServices->getCotizacionById($id);
            
            if (!$cotizacion) {
                return response()->json(['status' => 'error', 'message' => 'Cotización no encontrada'], 404);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $cotizacion
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener la cotización', $e);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'id_distribuidor'      => 'required|integer|exists:usuarios_distribuidores,id',
            'id_usuario_dicreme'   => 'required|integer|exists:usuarios_dicreme,id',
            'id_estado_cotizacion' => 'required|integer|exists:estados_cotizacion,id',
            'total_cotizacion'     => 'required|numeric',
            'persona_recibe'       => 'required|string'
        ]);

        try {
            $cotizacion = $this->cotizacionServices->updateCotizacion($id, $data);
            return response()->json([
                'status'  => 'success',
                'message' => 'Cotización actualizada',
                'data'    => $cotizacion
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al actualizar la cotización', $e);
        }
    }

    public function updateTotal(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'total_cotizacion' => 'required|numeric',
        ]);

        try {
            $resultado = $this->cotizacionServices->updateTotalCotizacion($id, $data['total_cotizacion']);
            return response()->json([
                'status'  => 'success',
                'message' => 'Total actualizado',
                'data'    => $resultado
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al actualizar el total', $e);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->cotizacionServices->deleteCotizacion($id);
            return response()->json([
                'status'  => 'success',
                'message' => 'Cotización eliminada correctamente'
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al eliminar la cotización', $e);
        }
    }

    public function transformarCotizacionEnPedido($idCotizacion): JsonResponse
    {
        try {
            $pedido = $this->cotizacionServices->transformarCotizacionEnPedido($idCotizacion);

            if ($pedido === false) { 
                return response()->json([
                    'status'  => 'error',
                    'message' => 'La cotización no está en estado completado o no se pudo procesar.',
                ], 400); 
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'La cotización ahora es un pedido',
                'data'    => $pedido
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() // Captura stock insuficiente
            ], 422);
        }
    }

    public function tomarcotizacion($id_cotizacion, $id_usuario_dicreme): JsonResponse
    {
        try {
            $cotizacion = $this->cotizacionServices->tomarcotizacionadmin($id_cotizacion, $id_usuario_dicreme);

            if ($cotizacion === false) {
                return response()->json(['status' => 'error', 'message' => 'La cotizacion no existe'], 404);
            }
            if ($cotizacion === null) {
                return response()->json(['status' => 'error', 'message' => 'El usuario no existe'], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'El administrador tomó la cotización correctamente.',
                'data'    => $cotizacion
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al tomar cotización', $e);
        }
    }

    public function dejarCotizacion($id, $id_usuario_dicreme): JsonResponse
    {
        try {
            $resultado = $this->cotizacionServices->Dejarcotizacionadmin($id, $id_usuario_dicreme);

            if ($resultado === false) {
                return response()->json(['status' => 'error', 'message' => 'No tienes permisos para liberar esta cotización.'], 403);
            }
            if ($resultado === null) {
                return response()->json(['status' => 'error', 'message' => 'La cotización solicitada no existe.'], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Cotización liberada correctamente.',
                'data'    => $resultado
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al dejar cotización', $e);
        }
    }

    public function cancelarCotizacion($id, $id_usuario): JsonResponse
    {
        try {
            $resultado = $this->cotizacionServices->Cancelarcotizacionadmin($id, $id_usuario);

            if ($resultado === false) {
                return response()->json(['status' => 'error', 'message' => 'No tienes permisos para cancelar esta cotización.'], 403);
            }
            if ($resultado === null) {
                return response()->json(['status' => 'error', 'message' => 'La cotización solicitada no existe.'], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'La cotización fue cancelada exitosamente',
                'data'    => $resultado
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al cancelar la cotización', $e);
        }
    }

    public function validarCotizacion(Request $request, $id, $id_usuario_dicreme): JsonResponse
    {
        $request->validate([
            'discountType' => 'required|in:percentage,fixed,none',
            'discountInput' => 'required|numeric|min:0', 
            'productos' => 'nullable|array',
            'productos.*.id_cotizacion_producto' => 'required|integer',
            'productos.*.discountType' => 'required|in:percentage,fixed,none',
            'productos.*.discountValue' => 'required|numeric|min:0', 
        ]);

        try {
            $this->cotizacionServices->validarCotizacion($id, $id_usuario_dicreme, $request->all());

            return response()->json([
                'status'  => 'success',
                'message' => '¡Cotización validada y pasada a estado Completado con éxito!'
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al validar la cotización', $e);
        }
    }

    public function getallCotizacionesByUsuariodistribuidor($id_usuario_distribuidor): JsonResponse
    {
        try {
            $cotizaciones = $this->cotizacionServices->getCotizacionesByUsuarioDistribuidor($id_usuario_distribuidor);
            return response()->json(['status' => 'success', 'data' => $cotizaciones], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener las cotizaciones del distribuidor', $e);
        }
    }

    public function getdetailcotizacion($id): JsonResponse
    {
        try {
            $resultado = $this->cotizacionServices->getDetailCotizacion($id);

            if ($resultado === false) {
                return response()->json(['status' => 'error', 'message' => 'No existe la cotización'], 404); 
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Detalles obtenidos exitosamente',
                'data'    => $resultado
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener el detalle', $e);
        }
    }

    public function addproductocotizacion(Request $request, $id_cotizacion): JsonResponse
    {
        $data = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id',
            'cantidad'    => 'required|integer|min:1',
        ]);

        try {
            $listaProductos = [['id_producto' => $data['id_producto'], 'cantidad' => $data['cantidad']]];
            $cotizacion = $this->cotizacionServices->add_productos_to_cotizacion($id_cotizacion, $listaProductos);

            return response()->json([
                'status'  => 'success',
                'message' => 'Producto añadido correctamente.',
                'data'    => $cotizacion
            ], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al añadir producto', $e);
        }
    }

    public function removeProductoCotizacion(Request $request, $id_cotizacion): JsonResponse
    {
        $data = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id',
            'cantidad'    => 'required|integer|min:1', 
        ]);

        try {
            $listaProductos = [['id_producto' => $data['id_producto'], 'cantidad' => $data['cantidad']]];
            $cotizacion = $this->cotizacionServices->remove_productos_to_cotizacion($id_cotizacion, $listaProductos);

            return response()->json([
                'status'  => 'success',
                'message' => 'Producto removido con éxito.',
                'data'    => $cotizacion
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function destroyProductoCotizacion(Request $request, $id_cotizacion): JsonResponse
    {
        $data = $request->validate([
            'id_producto' => 'required|integer|exists:productos,id',
        ]);

        try {
            $cotizacion = $this->cotizacionServices->force_remove_producto($id_cotizacion, $data['id_producto']);

            return response()->json([
                'status'  => 'success',
                'message' => 'Producto eliminado por completo.',
                'data'    => $cotizacion
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function getallCotizacionesByUsuariodicreme($id_usuario_dicreme): JsonResponse
    {
        try {
            $cotizaciones = $this->cotizacionServices->getCotizacionesByUsuario($id_usuario_dicreme);
            return response()->json(['status' => 'success', 'data' => $cotizaciones], 200);
        } catch (Exception $e) {
            return $this->errorResponse('Error al obtener cotizaciones del usuario', $e);
        }
    }

    /**
     * Función privada (Helper) para centralizar y estandarizar los errores 500.
     * Si la App está en debug (Local) te muestra el error real, si está en Producción lo oculta.
     */
    private function errorResponse(string $message, Exception $e): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'debug'   => config('app.debug') ? $e->getMessage() : 'Contacte al administrador'
        ], 500);
    }
}