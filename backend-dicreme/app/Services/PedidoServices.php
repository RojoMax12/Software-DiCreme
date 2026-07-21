<?php

namespace App\Services;

use App\Repositories\PedidoRepository;
use App\Repositories\Usuario_dicremeRepository;
use App\Repositories\Usuario_distribuidoresRepository;
use App\Repositories\DespachoRepository;
use App\Repositories\Pedido_productoRepository;
use App\Repositories\ProductoRepository;
use App\Repositories\CotizacionRepository;

class PedidoServices
{
    protected $pedidoRepository;
    protected $usuariodicremeRepository;
    protected $usuariodistribuidorRepository;
    protected $despachoRepository;
    protected $pedidoproductoRepository;
    protected $productoRepository;
    protected $cotizacionRepository;

    public function __construct(PedidoRepository $pedidoRepository, Usuario_dicremeRepository $usuariodicremeRepository, 
    Usuario_distribuidoresRepository $usuariodistribuidorRepository, 
    DespachoRepository $despachoRepository, Pedido_productoRepository $pedidoproductoRepository,
    ProductoRepository $productoRepository, CotizacionRepository $cotizacionRepository)
    {
        $this->pedidoRepository = $pedidoRepository;
        $this->usuariodicremeRepository = $usuariodicremeRepository;
        $this->usuariodistribuidorRepository = $usuariodistribuidorRepository;
        $this->despachoRepository = $despachoRepository;
        $this->pedidoproductoRepository = $pedidoproductoRepository;
        $this->productoRepository = $productoRepository;
        $this->cotizacionRepository = $cotizacionRepository;

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
        $pedido['id_estado_pago'] = 1;
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


    public function actualizarEstadoPedido($id_pedido, $idNuevoEstado = null)
    {   
        // 1. Buscamos el pedido para saber en qué estado se encuentra hoy
        $pedido = $this->pedidoRepository->getPedidoById($id_pedido);
        if (!$pedido) {
            throw new \Exception("El pedido #{$id_pedido} no existe.");
        }

        $estadoActual = (int)$pedido->id_estado_pedido;
        
        if ($idNuevoEstado === null) {
            $idNuevoEstado = $estadoActual + 1;
        }
        
        $idNuevoEstado = (int)$idNuevoEstado;

        // Buscamos el despacho asociado
        $despacho = $this->despachoRepository->getDespachoByIdpedido($id_pedido);

        // 3. EFECTOS SECUNDARIOS: Sincronización con despacho
        if ($idNuevoEstado === 4) { // En Despacho
            if ($despacho) {
                $this->despachoRepository->updateDespacho($despacho->id, [
                    "id_estado_despacho" => 3 // En ruta
                ]);
            }
        }

        if ($idNuevoEstado === 5) { // Entregado
            if ($despacho) {
                $this->despachoRepository->updateDespacho($despacho->id, [
                    "id_estado_despacho" => 4, // Entrega exitosa
                    'fecha_entrega'   => now()
                ]);
            }
        }

        // 5. ACTUALIZACIÓN FINAL: Guardamos el cambio en el repositorio mudo
        return $this->pedidoRepository->updatePedido($id_pedido, [
            'id_estado_pedido' => $idNuevoEstado
        ]);
    }

    public function actualizarEstadoPago($id_pedido, $idNuevoEstado = null)
    {   
        // 1. Buscamos el pedido para saber en qué estado se encuentra hoy
        $pedido = $this->pedidoRepository->getPedidoById($id_pedido);
        if (!$pedido) {
            return null; // El pedido no existe
        }

        $estadoActual = (int)$pedido->id_estado_pago;
        
        if ($idNuevoEstado === null) {
            $idNuevoEstado = $estadoActual + 1;
        }
        
        $idNuevoEstado = (int)$idNuevoEstado;

        return $this->pedidoRepository->updatePedido($id_pedido, [
            'id_estado_pago' => $idNuevoEstado
        ]);
    }


    public function getDetailPedido($id)
    {
        // 1. Buscamos la cotización base
        $pedido = $this->pedidoRepository->getPedidoById($id);

        if (!$pedido) {
            return false; // No existe
        }

        // 2. Traemos las relaciones del distribuidor y los registros pivote
        $usuario_distribuidor = $this->usuariodistribuidorRepository->getUsuarioDistribuidorById($pedido->id_usuario_distribuidor);
        $cotizacion = $this->cotizacionRepository->getCotizacionById($pedido->id_cotizacion);
        $pedido_productos= $this->pedidoproductoRepository->getPedidoProductosByPedidoId($pedido->id);
        $despacho = $this->despachoRepository->getDespachoByIdpedido($pedido->id);

        // 3. RECORREMOS LA LISTA: Combinamos el pivote con la info del catálogo del producto
        $listaProductosData = [];

        foreach ($pedido_productos as $itemPivote) {
            // Buscamos el detalle del producto usando el id_producto de la fila intermedia
            $infoProducto = $this->productoRepository->getProductoById($itemPivote->id_producto);

            if ($infoProducto) {
                $categoria = \App\Models\Categoria::find($infoProducto->id_categoria);
                $formato = \App\Models\Formato::find($infoProducto->id_formato);

                $listaProductosData[] = [
                    'id_producto'           => $infoProducto->id,
                    'nombre_producto'       => $infoProducto->nombre_producto,
                    'nombre_categoria'      => $categoria ? $categoria->nombre_categoria : '',
                    'nombre_formato'        => $formato ? $formato->nombre_formato : '',
                    'id_categoria'          => $infoProducto->id_categoria,
                    'id_formato'            => $infoProducto->id_formato,
                    'cantidad'              => $itemPivote->cantidad,
                    'precio_unitario_venta' => $itemPivote->precio_unitario_venta,
                    'subtotal'              => $itemPivote->cantidad * $itemPivote->precio_unitario_venta
                ];
            }
        }

        // 4. RETORNAMOS EL PAQUETE COMPLETO
        return [
            'id_pedido'            => $pedido->id,
            'persona_recibe'       => $despacho ? $despacho->persona_recibe : ($cotizacion ? $cotizacion->persona_recibe : ''),
            'direccion_entrega'    => $despacho ? $despacho->direccion_entrega : ($usuario_distribuidor ? $usuario_distribuidor->direccion : ''),
            'comuna'               => $despacho ? $despacho->comuna : ($usuario_distribuidor ? $usuario_distribuidor->comuna : ''),
            'total_cotizacion'     => $pedido->monto_final,
            'subtotal_cotizacion'  => $pedido->monto_estimado,
            'id_estado_pedido'     => $pedido->id_estado_pedido,
            'id_estado_pago'       => $pedido->id_estado_pago,
            'fecha_creacion'       => $pedido->fecha_creacion,
            'hora_creacion'        => $pedido->hora_creacion,
            'descuento_total'      => $cotizacion ? ($cotizacion->descuento_general_aplicado + $cotizacion->descuento_productos_total) : 0,

            // Objeto con la información del distribuidor
            'distribuidor'         => $usuario_distribuidor, 
            'despacho'             => $despacho,
            
            // Array estructurado con sus productos, cantidades y nombres reales
            'productos'            => $listaProductosData
        ];
    }
    

}