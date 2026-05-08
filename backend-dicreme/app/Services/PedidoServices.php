<?php

namespace App\Services;
use App\Repositories\PedidoRepository;
use App\Models\Pedido;

class PedidoServices
{
    protected $pedidoRepository;

    public function __construct(PedidoRepository $pedidoRepository)
    {
        $this->pedidoRepository = $pedidoRepository;
    }

    public function getAllPedidos()
    {
        return $this->pedidoRepository->getAllPedidos();
    }

    public function getPedidoById($id)
    {
        return $this->pedidoRepository->getPedidoById($id);
    }

    public function createPedido($data)
    {
        return $this->pedidoRepository->createPedido($data);
    }

    public function updatePedido($id, $data)
    {
        return $this->pedidoRepository->updatePedido($id, $data);
    }

    public function deletePedido($id)
    {
        return $this->pedidoRepository->deletePedido($id);
    }
}