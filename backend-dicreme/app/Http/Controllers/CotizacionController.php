<?php

namespace App\Http\Controllers;
use App\Services\CotizacionServices;
use Illuminate\Http\Request;

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
        $data = $request->validate([
            'id_cotizacion' => 'required|integer|exists:cotizaciones,id',
            'id_usuario_dicreme' => 'required | integer| exists:usuarios_dicreme, id',
            'id_producto' => 'required|integer|exists:productos,id',
            'cantidad' => 'required|integer',
            'precio_unitario_venta' => 'required|numeric',
            'total_cotizacion' => 'required|integer',
        ]);

        return response()->json($this->cotizacionServices->createCotizacion($data), 201);
    }

    public function show($id)
    {   
        return response()->json($this->cotizacionServices->getCotizacionById($id));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_cotizacion' => 'sometimes|required|integer|exists:cotizaciones,id',
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

}