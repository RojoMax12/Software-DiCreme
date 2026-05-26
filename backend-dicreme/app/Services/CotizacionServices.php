<?php

namespace App\Services;
use App\Repositories\CotizacionRepository;
use App\Repositories\PedidoRepository;         // <--- Inyectamos el Repositorio de Pedidos
use App\Repositories\Pedido_productoRepository; // <--- Inyectamos el Repositorio del Detalle
use App\Repositories\Usuario_dicremeRepository;

class CotizacionServices
{
    protected $cotizacionRepository;
    protected $pedidoRepository;          // <--- Repositorio de Pedidos
    protected $pedidoProductoRepository;  // <--- Repositorio del Detalle
    protected $usuariodicremeRepository;

    public function __construct(CotizacionRepository $cotizacionRepository , PedidoRepository $pedidoRepository, Pedido_productoRepository $pedidoProductoRepository, Usuario_dicremeRepository $usuariodicremeRepository)
    {
        $this->cotizacionRepository = $cotizacionRepository;
        $this->pedidoRepository = $pedidoRepository;
        $this->pedidoProductoRepository = $pedidoProductoRepository;
        $this->usuariodicremeRepository = $usuariodicremeRepository;
    }

    public function createCotizacion($data)
    {
        return $this->cotizacionRepository->createCotizacion($data);
    }

    public function getCotizacionById($id)
    {
        return $this->cotizacionRepository->getCotizacionById($id);
    }

    public function updateCotizacion($id, $data)
    {
        return $this->cotizacionRepository->updateCotizacion($id, $data);
    }

    public function deleteCotizacion($id)
    {
        return $this->cotizacionRepository->deleteCotizacion($id);
    }

    public function getAllCotizaciones()
    {
        return $this->cotizacionRepository->getAllCotizaciones();
    }

    public function transformarCotizacionEnPedido($idCotizacion)
    {
    
        $cotizacion = $this->cotizacionRepository->getCotizacionConProductos($idCotizacion);

        if (!$cotizacion) {
            throw new \Exception("La cotización no existe.");
        }

        $pedido = $this->pedidoRepository->createPedido([
            'id_cotizacion'    => $cotizacion->id,
            'id_usuario_dicreme' => $cotizacion->id_usuario_dicreme,
            'id_estado_pedido' => 1, 
            'fecha_creacion'   => now(),  
            'monto_estimado'    => $cotizacion->total_cotizacion,
            'monto_final'    => $cotizacion->total_cotizacion,
            ]);

        foreach ($cotizacion->cotizacionProductos as $itemIntermedio) {
            $this->pedidoProductoRepository->createPedidoProducto([
                'id_pedido'    => $pedido->id,
                'id_producto'  => $itemIntermedio->id,
                'cantidad'     => $itemIntermedio->cantidad,
                'precio_venta' => $itemIntermedio->precio_cotizado, 
            ]);
        }

        $this->cotizacionRepository->updateCotizacion($cotizacion->id, [
            'estado_cotizacion' => 'Validada'
        ]);

        return $pedido;
    }

    public function getCotizacionesByUsuario($id_usuario_dicreme) {

        $usuario_dicreme = $this->usuariodicremeRepository->getUsuarioDicremeById($id_usuario_dicreme);

        if(!$usuario_dicreme){
            throw new \Exception("El usuario no existe.");
        }
        
        return $this->cotizacionRepository->getCotizacionesByUsuario($id_usuario_dicreme);
    }

}
