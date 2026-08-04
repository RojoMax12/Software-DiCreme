<?php

namespace App\Http\Controllers;

use App\Services\Estado_despachoServices;

class Estado_despachoController
{
    protected $estado_despachoServices;

    public function __construct(Estado_despachoServices $estado_despachoServices)
    {
        $this->estado_despachoServices = $estado_despachoServices;
    }

    public function index()
    {
        $estados_despacho = $this->estado_despachoServices->getAll();
        return response()->json($estados_despacho);
    }

    public function show($id)
    {
        $estado_despacho = $this->estado_despachoServices->getById($id);
        return response()->json($estado_despacho);
    }

    public function store(Request $request)
    {
        $estado_despacho = $this->estado_despachoServices->create($request->all());
        return response()->json($estado_despacho);
    }

    public function update(Request $request, $id)
    {
        $estado_despacho = $this->estado_despachoServices->update($id, $request->all());
        return response()->json($estado_despacho);
    }

    public function destroy($id)
    {
        $estado_despacho = $this->estado_despachoServices->delete($id);
        return response()->json($estado_despacho);
    }
}