<?php

namespace App\Services;

use App\Repositories\PedidoRepository;
use App\Repositories\Usuario_dicremeRepository;
use App\Repositories\Usuario_distribuidoresRepository;
use App\Repositories\DespachoRepository;

class PedidoServices
{
    protected $pedidoRepository;
    protected $usuariodicremeRepository;
    protected $usuariodistribuidorRepository;
    protected $despachoRepository;

    public function __construct(PedidoRepository $pedidoRepository, Usuario_dicremeRepository $usuariodicremeRepository, 
    Usuario_distribuidoresRepository $usuariodistribuidorRepository, DespachoRepository $despachoRepository)
    {
        $this->pedidoRepository = $pedidoRepository;
        $this->usuariodicremeRepository = $usuariodicremeRepository;
        $this->usuariodistribuidorRepository = $usuariodistribuidorRepository;
        $this->despachoRepository = $despachoRepository;

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

    public function getPedidoByUsuario_distribuidores($id_usuario_distribuidor){

        $usuario_distribuidor = $this->usuariodistribuidorRepository->getUsuarioDistribuidorById($id_usuario_distribuidor);

        if(!$usuario_distribuidor){
            throw new \Exception("El usuario no existe.");
        }
        return $this->pedidoRepository->getPedidoByUsuario_distribuidores($id_usuario_distribuidor);
    }


    public function actualizarEstadoPedido($id_pedido)
    {   
        // 1. Buscamos el pedido para saber en qué estado se encuentra hoy
        $pedido = $this->pedidoRepository->getPedidoById($id_pedido);
        if (!$pedido) {
            return null; // El pedido no existe
        }

        $estadoActual = (int)$pedido->id_estado_pedido;
        
        // 2. FRENO DE SEGURIDAD MÁXIMO: Si ya está entregado (estado 4), no puede avanzar a 5
        if ($estadoActual >= 4) {
            // Retornamos el pedido tal cual está para que el controlador sepa que no se alteró
            return $pedido; 
        }

        // Buscamos el despacho asociado
        $despacho = $this->despachoRepository->getDespachoByIdpedido($id_pedido);

        $idNuevoEstado = $estadoActual + 1;
        // 3. EFECTOS SECUNDARIOS: Si está en 3 (Preparación) y va a avanzar a 4 (Despachado)
        if ($idNuevoEstado === 3) {
            if ($despacho) {
                $this->despachoRepository->updateDespacho($despacho->id, [
                    "estado_despacho" => "despachado"
                ]);
            }
        }

        // 4. EFECTOS SECUNDARIOS: Si el nuevo estado que va a asumir va a ser el de Entrega
        // Nota: Si el flujo avanza de 3 a 4, tu condición original de la fecha de entrega
        // debería ejecutarse cuando efectivamente pasa a estar en manos del cliente.


        if ($idNuevoEstado === 4) {
            if ($despacho) {
                $this->despachoRepository->updateDespacho($despacho->id, [
                    "estado_despacho" => "entregado",
                    'fecha_entrega'   => now()
                ]);
            }
        }

        // 5. ACTUALIZACIÓN FINAL: Guardamos el cambio en el repositorio mudo
        return $this->pedidoRepository->updatePedido($id_pedido, [
            'id_estado_pedido' => $idNuevoEstado
        ]);
    }

}