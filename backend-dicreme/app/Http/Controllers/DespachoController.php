<?php

namespace App\Http\Controllers;
use App\Services\DespachoServices;
use Illuminate\Http\Request;

class DespachoController extends Controller
{
    protected $despachoServices;

    public function __construct(DespachoServices $despachoServices)
    {
        $this->despachoServices = $despachoServices;
    }

    public function index()
    {
        return response()->json($this->despachoServices->getAllDespachos());
    }

    public function show($id)
    {
        return response()->json($this->despachoServices->getDespachoById($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos,id',
            'direccion_entrega' => 'required|string|max:255',
            'fecha_entrega' => 'required|date',
            'persona_recibe' => 'required|string|max:255',
            'comuna' => 'required|string|max:255',
            'estado_despacho' => 'required|string|max:40',
            'id_usuario_dicreme'=> 'required|integer|exists:usuario_dicreme,id'
        ]);
        return response()->json($this->despachoServices->createDespacho($data));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'id_pedido' => 'required|integer|exists:pedidos,id',
            'direccion_entrega' => 'required|string|max:255',
            'fecha_entrega' => 'required|date',
            'persona_recibe' => 'required|string|max:255',
            'comuna' => 'required|string|max:255',
            'estado_despacho' => 'required|string|max:40',
            'id_usuario_dicreme'=> 'required|integer|exists:usuario_dicreme,id'
        ]);
        return response()->json($this->despachoServices->updateDespacho($id, $data));
    }

    public function destroy($id)
    {
        return response()->json($this->despachoServices->deleteDespacho($id));
    }

    public function getdespachobyidpedido($id){
        return response()->json($this->despachoServices->despachosbyidpedido($id));
    }

    public function getdespachobyidusuariodicreme($id){

        $despachos = $this->despachoServices->getDespachoByIdusuariodicreme($id);

        if($despachos === null){
            return response()->json([
                'status'  => 'error',
                'message' => 'No existe el despachos asignados a este despachador',
            ], 400); 
        }

        if($despachos === false){
            return response()->json([
                'status'  => 'error',
                'message' => 'El usuario de dicreme no es un despachador',
            ], 400); 

        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Estos son los despachos afiliados al despachador',
            'data'    => $despachos
        ], 200);
    }
        
    public function asignardespachoadespachador($id_despacho, $id_despachador){

        $despacho = $this->despachoServices->asignardespachoaldespachador($id_despacho, $id_despachador);

        if($despacho === null){
            return response()->json([
                'status'  => 'error',
                'message' => 'No existe el despachos',
            ], 400); 
        }

        if($despacho === false){
            return response()->json([
                'status'  => 'error',
                'message' => 'El usuario de dicreme no es un despachador o no existe el usuario',
            ], 400); 

        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Despachador asignado correctamente al despacho',
            'data'    => $despacho
        ], 200);

    }
}