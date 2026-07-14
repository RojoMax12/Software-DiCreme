<?php

namespace App\Http\Controllers;
use App\Services\Cotizacion_productoServices;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class Cotizacion_productoController extends Controller
{
    protected $cotizacionProductoServices;

    public function __construct(Cotizacion_productoServices $cotizacionProductoServices)
    {
        $this->cotizacionProductoServices = $cotizacionProductoServices;
    }


    public function store(Request $request):JsonResponse
    {
        $data = $request->validate([
            'id_cotizacion' => 'required|integer|exists:cotizaciones,id',
            'id_producto' => 'required|integer|exists:productos,id',
            'cantidad' => 'required|integer',
            'precio_unitario_venta' => 'required|numeric',
        ]);

        try {
            
            $cotizacion_producto = $this->cotizacionProductoServices->createCotizacionProducto($data);

            return response()->json([
                'status' => 'success',
                'data' => $cotizacion_producto,
                'message' => 'La cotizacion se afilio correctamente con el producto'
            ], 201);
        } catch (\Exception $e) {
            
        return response()->json([
                'status' => 'error',
                'message' => 'La cotizacion no se pudo afiliar correctamente con el producto ' . $e->getMessage()
            ], 400);
        }

    }


    public function update(Request $request, $id):JsonResponse
    {
        $data = $request->validate([
            'id_cotizacion' => 'sometimes|required|exists:cotizaciones,id',
            'id_producto' => 'sometimes|required|exists:productos,id',
            'cantidad' => 'sometimes|required|integer',
            'precio_unitario_venta' => 'sometimes|required|numeric',
        ]);

        try {
            
            $cotizacion_producto_update = $this->cotizacionProductoServices->updateCotizacionProducto($id, $data);

            return response()->json([
                'status' => 'success',
                'data' => $cotizacion_producto_update,
                'message' => 'Se actualizo correctamente el producto asociado a la cotizacion'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo actualizo correctamente el producto asociado a la cotizacion ' . $e->getMessage()
            ], 400);
        }


    }


    public function destroy($id):JsonResponse
    {   
        try {
            $cotizacion_producto_eliminar = $this->cotizacionProductoServices->deleteCotizacionProducto($id);

            return response()->json([
                'status' => 'success',
                'data' => $cotizacion_producto_eliminar,
                'message' => 'Se elimino el producto asociado a la cotizacion'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo eliminar correctamente el producto asociado a la cotizacion ' . $e->getMessage()
            ], 400);
        }
    }

      public function show($id)
    {
        return response()->json($this->cotizacionProductoServices->getCotizacionProductoById($id));
    }

    public function getByCotizacionId($idCotizacion)
    {
        return response()->json($this->cotizacionProductoServices->getCotizacionProductosByCotizacionId($idCotizacion));
    }

    public function getByProductoId($idProducto)
    {
        return response()->json($this->cotizacionProductoServices->getCotizacionProductosByProductoId($idProducto));
    }

    public function index()
    {
        return response()->json($this->cotizacionProductoServices->getAllCotizacionProductos());
    }

}