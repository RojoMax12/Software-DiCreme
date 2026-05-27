<?php

namespace App\Http\Controllers;
use App\Services\CotizacionServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CotizacionController extends Controller
{
    protected $cotizacionServices;

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
            'id_usuario_dicreme' => 'required | integer| exists:usuarios_dicreme, id',
            'id_producto' => 'sometimes|required|integer|exists:productos,id',
            'cantidad' => 'sometimes|required|integer',
            'precio_unitario_venta' => 'sometimes|required|numeric',
            'total_cotizacion' => 'sometimes|required|integer',
        ]);

        return response()->json($this->cotizacionServices->updateCotizacion($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->cotizacionServices->deleteCotizacion($id));
    }

    public function transformarCotizacionEnPedido($idCotizacion)
    {
        try {
            $pedido = $this->cotizacionServices->transformarCotizacionEnPedido($idCotizacion);
            return response()->json($pedido, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getallCotizacionesByUsuariodicreme($id_usuario_dicreme)
    {
        return response()->json($this->cotizacionServices->getCotizacionesByUsuario($id_usuario_dicreme));

    }

    public function getallCotizacionesByUsuariodistribuidor($id_usuario_distribuidor){
        return response()->json($this->cotizacionServices->getCotizacionesByUsuarioDistribuidor($id_usuario_distribuidor));
    }

}