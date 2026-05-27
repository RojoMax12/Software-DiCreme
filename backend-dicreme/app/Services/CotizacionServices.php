<?php

namespace App\Services;
use App\Repositories\CotizacionRepository;
use App\Repositories\PedidoRepository;         // <--- Inyectamos el Repositorio de Pedidos
use App\Repositories\Pedido_productoRepository; // <--- Inyectamos el Repositorio del Detalle
use App\Repositories\Usuario_dicremeRepository;
use App\Repositories\Usuario_distribuidoresRepository;
use App\Models\Cotizacion;

class CotizacionServices
{
    protected $cotizacionRepository;
    protected $pedidoRepository;          // <--- Repositorio de Pedidos
    protected $pedidoProductoRepository;  // <--- Repositorio del Detalle
    protected $usuariodicremeRepository;
    protected $usuario_distribuidoresRepository;

    public function __construct(CotizacionRepository $cotizacionRepository , PedidoRepository $pedidoRepository, 
    Pedido_productoRepository $pedidoProductoRepository, Usuario_dicremeRepository $usuariodicremeRepository,Usuario_distribuidoresRepository $usuario_distribuidoresRepository)
    {
        $this->cotizacionRepository = $cotizacionRepository;
        $this->pedidoRepository = $pedidoRepository;
        $this->pedidoProductoRepository = $pedidoProductoRepository;
        $this->usuariodicremeRepository = $usuariodicremeRepository;
        $this->usuario_distribuidoresRepository = $usuario_distribuidoresRepository;
    }

    public function createCotizacion(array $data)
    {
        // 1. Creamos la cotización maestra
        $cotizacion = Cotizacion::create([
            'id_distribuidor'      => $data['id_distribuidor'],
            'id_usuario_dicreme'   => $data['id_usuario_dicreme'] ?? null,
            'id_estado_cotizacion' => $data['id_estado_cotizacion'],
            'fecha_creacion'       => $data['fecha_creacion'],
            'hora_creacion'        => $data['hora_creacion'],
            'total_cotizacion'     => $data['total_cotizacion']
        ]);

        // 2. Creamos los detalles asociados
        foreach ($data['cotizacion_productos'] as $producto) {
            $cotizacion->cotizacionProductos()->create($producto);
        }

        return $cotizacion->load('cotizacionProductos');
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

        // Creamos el pedido
        $pedido = $this->pedidoRepository->createPedido([
            'id_cotizacion'      => $cotizacion->id,
            'id_usuario_dicreme' => $cotizacion->id_usuario_dicreme,
            'id_estado_pedido'   => 1, // Estado inicial
            'fecha_pedido'       => now(), 
            'monto_estimado'     => $cotizacion->total_cotizacion,
            'monto_final'        => $cotizacion->total_cotizacion,
        ]);

        // Clonamos los productos
        foreach ($cotizacion->cotizacionProductos as $item) {
            $this->pedidoProductoRepository->createPedidoProducto([
                'id_pedido'    => $pedido->id,
                'id_producto'  => $item->id_producto, // CORRECCIÓN: Usar ID del producto, no el de la cotización
                'cantidad'     => $item->cantidad,
                'precio_venta' => $item->precio_cotizado, 
            ]);
        }

        // Actualizamos la cotización (Usando el nombre correcto de la columna)
        $this->cotizacionRepository->updateCotizacion($cotizacion->id, [
            'id_estado_cotizacion' => 2 // Suponiendo que 2 es el estado "Validada"
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


    public function getCotizacionesByUsuarioDistribuidor($id_usuario_distribuidor) {

        $usuario_distribuidor = $this->usuario_distribuidoresRepository->getUsuarioDistribuidorById($id_usuario_distribuidor);

        if(!$usuario_distribuidor){
            throw new \Exception("El usuario no existe.");
        }
        
        return $this->cotizacionRepository->getCotizacionesByUsuarioDistribuidor($id_usuario_distribuidor);
    }

}
