<?php

namespace App\Services;
use App\Repositories\PedidoRepository;
use App\Repositories\Usuario_dicremeRepository;

class PedidoServices
{
    protected $pedidoRepository;
    protected $usuariodicremeRepository;

    public function __construct(PedidoRepository $pedidoRepository, Usuario_dicremeRepository $usuariodicremeRepository)
    {
        $this->pedidoRepository = $pedidoRepository;
        $this->usuariodicremeRepository = $usuariodicremeRepository;

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

    public function getPedidoByUsuario($id_usuario_dicreme) {

        $usuario_dicreme = $this->usuariodicremeRepository->getUsuarioDicremeById($id_usuario_dicreme);

       if(!$usuario_dicreme){
            throw new \Exception("El usuario no existe.");
        }
    
        return $this->pedidoRepository->getPedidoByUsuario($id_usuario_dicreme);
    }


}