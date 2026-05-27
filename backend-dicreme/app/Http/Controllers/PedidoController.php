<?php

namespace App\Http\Controllers;
use App\Services\PedidoServices;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    protected $pedidoService;

    public function __construct(PedidoServices $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    public function index()
    {
        return response()->json($this->pedidoService->getAllPedidos());
    }

    public function show($id)
    {
        return response()->json($this->pedidoService->getPedidoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_cotizacion' => 'required|integer|exists:cotizaciones,id',
            'fecha_creacion' => 'required|date',
            'id_estado_pedido' => 'required|integer|exists:estados_pedido,id',
            'id_usuario_dicreme' => 'sometimes|integer|exists:usuarios_dicreme,id',
            'monto_estimado' => 'required|integer',
            'monto_final' => 'required|integer',
        ]);

        return response()->json($this->pedidoService->createPedido($data), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_cotizacion' => 'sometimes|required|integer|exists:cotizaciones,id',
            'fecha_creacion' => 'sometimes|required|date',
            'id_estado_pedido' => 'sometimes|required|integer|exists:estados_pedido,id',
            'id_usuario_dicreme' => 'sometimes|integer|exists:usuarios_dicreme,id',
            'monto_estimado' => 'sometimes|required|integer',
            'monto_final' => 'sometimes|required|integer',
        ]);

        return response()->json($this->pedidoService->updatePedido($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->pedidoService->deletePedido($id));
    }

    public function getallPedidosByUsuariodicreme($id_usuario_dicreme)
    {
        return response() ->json($this->pedidoService->getPedidoByUsuario($id_usuario_dicreme));
    }
}