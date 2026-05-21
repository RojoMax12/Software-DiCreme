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
            'id_cotizacion' => 'required|integer',
            'id_producto' => 'required|integer',
            'cantidad' => 'required|integer',
            'precio_unitario_venta' => 'required|numeric',
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
            'id_cotizacion' => 'sometimes|required|integer',
            'id_producto' => 'sometimes|required|integer',
            'cantidad' => 'sometimes|required|integer',
            'precio_unitario_venta' => 'sometimes|required|numeric',
        ]);

        return response()->json($this->cotizacionServices->updateCotizacion($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->cotizacionServices->deleteCotizacion($id));
    }
}