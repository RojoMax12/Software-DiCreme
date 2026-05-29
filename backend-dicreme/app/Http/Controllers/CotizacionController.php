<?php

namespace App\Http\Controllers;
use App\Services\CotizacionServices;
use Illuminate\Http\Request;


class CotizacionController extends Controller
{
    protected $cotizacionServices;
    protected $usuario_dicremeServices;

    public function __construct(CotizacionServices $cotizacionServices)
    {
        $this->cotizacionServices = $cotizacionServices;
    }

    public function index()
    {
        return response()->json($this->cotizacionServices->getAllCotizaciones());
    }

    public function store(Request $request)
    {
        // Validamos solo los campos que existen en la tabla y los que son necesarios
        $data = $request->validate([
            'id_distribuidor'      => 'required|integer|exists:usuarios_distribuidores,id',
            'id_usuario_dicreme'   => 'nullable|integer|exists:usuarios_dicreme,id',
            'id_estado_cotizacion' => 'required|integer|exists:estados_cotizacion,id',
            'total_cotizacion'     => 'required|numeric', // Cambiado a numeric para ser preciso
            'persona_recibe' => 'required|string',
            
            // Validamos el array de productos que viene en el JSON
            'cotizacion_productos' => 'required|array|min:1',
            'cotizacion_productos.*.id_producto'     => 'required|integer|exists:productos,id',
            'cotizacion_productos.*.cantidad'        => 'required|integer|min:1',
            'cotizacion_productos.*.precio_unitario_venta' => 'required|numeric',
        ]);

        // Agregamos las fechas que el usuario NO envía, pero que el sistema debe registrar
        $data['fecha_creacion'] = now()->toDateString();
        $data['hora_creacion']  = now()->toTimeString();

        return response()->json($this->cotizacionServices->createCotizacion($data), 201);
    }

    public function show($id)
    {   
        return response()->json($this->cotizacionServices->getCotizacionById($id));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_distribuidor' => 'required|integer|exists:usuarios_distribuidores,id',
            'id_usuario_dicreme' => 'required | integer| exists:usuarios_dicreme, id',
            'id_estado_cotizacion' => 'required|integer|exists:estados_cotizacion,id',
            'total_cotizacion'     => 'required|numeric',
            'fecha_creacion',
            'hora_creacion',
            'persona_recibe' => 'required|string'
        ]);

        return response()->json($this->cotizacionServices->updateCotizacion($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->cotizacionServices->deleteCotizacion($id));
    }

    public function transformarCotizacionEnPedido($idCotizacion)
    {   
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
    }

    public function getallCotizacionesByUsuariodicreme($id_usuario_dicreme)
    {
        return response()->json($this->cotizacionServices->getCotizacionesByUsuario($id_usuario_dicreme));

    }

    public function tomarcotizacion($id_cotizacion, $id_usuario_dicreme){
        
    $cotizacionActualizada = $this->cotizacionServices->tomarcotizacionadmin($id_cotizacion, $id_usuario_dicreme);

        if($cotizacionActualizada === false){
            response()->json([
            'status'  => 'error',
            'message' => 'La cotizacion no existe',
        ], 404);
        }

        if($cotizacionActualizada === null){
            response()->json([
            'status'  => 'error',
            'message' => 'el usuario no existe',
        ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'El administrador tomó la cotización correctamente.',
            'data'    => $cotizacionActualizada
        ], 200);

    }


    public function dejarCotizacion($id, $id_usuario_dicreme) 
    {
        $resultado = $this->cotizacionServices->Dejarcotizacionadmin($id, $id_usuario_dicreme);

        if ($resultado === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permisos para liberar esta cotización porque está asignada a otro administrador.'
            ], 403); // 403 Forbidden (Prohibido)
        }

        if ($resultado === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'La cotización solicitada no existe.'
            ], 404); // 404 Not Found
        }

        // Éxito total
        return response()->json([
            'status' => 'success',
            'message' => 'La cotización fue liberada correctamente y vuelve a estar disponible.',
            'data' => $resultado
        ], 200); // 200 OK
    }   


    public function cancelarCotizacion($id, $id_usuario) 
    {
        $resultado = $this->cotizacionServices->Cancelarcotizacionadmin($id, $id_usuario);

        if ($resultado === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permisos para cancelar esta cotización porque está asignada a otro administrador.'
            ], 403); // 403 Forbidden (Prohibido)
        }

        if ($resultado === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'La cotización solicitada no existe.'
            ], 404); // 404 Not Found
        }

        // Éxito total
        return response()->json([
            'status' => 'success',
            'message' => 'La cotización fue cancelada exitosamente',
            'data' => $resultado
        ], 200); // 200 OK
    }

    public function validarCotizacion($id, $id_usuario_dicreme){

        $resultado = $this->cotizacionServices->validarCotizacion($id, $id_usuario_dicreme);

        if( $resultado === false){
            return response()->json([
                'status' => 'error',
                'message' => 'La cotizacion no se pudo validar, ya que no esta en estado de revision'
            ], 403); // 403 Forbidden (Prohibido)

        }

        if( $resultado === null){
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permisos para validar esta cotización porque está asignada a otro administrador.'
            ], 403); 
        
        }

        return response()->json([
            'status' => 'success',
            'message' => 'La cotización fue validada',
            'data' => $resultado
        ], 200); // 200 OK

    }

    public function getallCotizacionesByUsuariodistribuidor($id_usuario_distribuidor){
        return response()->json($this->cotizacionServices->getCotizacionesByUsuarioDistribuidor($id_usuario_distribuidor));
    }

}