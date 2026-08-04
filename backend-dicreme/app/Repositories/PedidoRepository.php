<?php

namespace App\Repositories;
use App\Models\Pedido;

class PedidoRepository {
    public function getAllPedidos($fecha = null)
    {
        $query = Pedido::query();
        if ($fecha) {
            $query->whereDate('fecha_creacion', $fecha);
        }
        return $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();
    }

    public function getPedidoById($id)
    {
        return Pedido::find($id);
    }
    public function createPedido($data)
    {
        return Pedido::create($data);
    }

    public function updatePedido($id, $data)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->update($data);
            return $pedido;
        }
        return null;
    }

    public function deletePedido($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            $pedido->delete();
            return true;
        }
        return false;
    }

     public function getPedidoByUsuario($id)
    { 
        return Pedido::where('id_usuario_dicreme', $id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();
    }

    public function getPedidoByUsuario_distribuidores($id){
        return Pedido::where('id_usuario_distribuidor', $id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();
    }

}