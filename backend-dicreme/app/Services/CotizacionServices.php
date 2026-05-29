<?php

namespace App\Services;
use App\Repositories\CotizacionRepository;
use App\Repositories\PedidoRepository;         // <--- Inyectamos el Repositorio de Pedidos
use App\Repositories\Pedido_productoRepository; // <--- Inyectamos el Repositorio del Detalle
use App\Repositories\Usuario_dicremeRepository;
use App\Repositories\Usuario_distribuidoresRepository;
use App\Repositories\DespachoRepository;
use App\Models\Cotizacion;

class CotizacionServices
{
    protected $cotizacionRepository;
    protected $pedidoRepository;          // <--- Repositorio de Pedidos
    protected $pedidoProductoRepository;  // <--- Repositorio del Detalle
    protected $usuariodicremeRepository;
    protected $usuario_distribuidoresRepository;
    protected $despachorepository;

    public function __construct(CotizacionRepository $cotizacionRepository , PedidoRepository $pedidoRepository, 
    Pedido_productoRepository $pedidoProductoRepository, Usuario_dicremeRepository $usuariodicremeRepository,
    Usuario_distribuidoresRepository $usuario_distribuidoresRepository, DespachoRepository $despachorepository)
    {
        $this->cotizacionRepository = $cotizacionRepository;
        $this->pedidoRepository = $pedidoRepository;
        $this->pedidoProductoRepository = $pedidoProductoRepository;
        $this->usuariodicremeRepository = $usuariodicremeRepository;
        $this->usuario_distribuidoresRepository = $usuario_distribuidoresRepository;
        $this->despachorepository = $despachorepository;
    }

    public function createCotizacion(array $data)
    {
        // 1. Creamos la cotización maestra
        $cotizacion = Cotizacion::create([
            'id_distribuidor'      => $data['id_distribuidor'],
            'id_usuario_dicreme'   => $data['id_usuario_dicreme'] ?? null,
            'id_estado_cotizacion' => $data['id_estado_cotizacion'],
            'persona_recibe' => $data['persona_recibe'],
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
        $usuario = $this->usuario_distribuidoresRepository->getUsuarioDistribuidorById($cotizacion->id_distribuidor);

        if (!$cotizacion) {
            throw new \Exception("La cotización no existe.");
        }
        
        if($cotizacion->id_estado_cotizacion !== 3){
            return false;
        }

        // Creamos el pedido
        $pedido = $this->pedidoRepository->createPedido([
            'id_cotizacion'      => $cotizacion->id,
            'id_usuario_dicreme' => $cotizacion->id_usuario_dicreme,
            'id_usuario_distribuidor' => $cotizacion->id_distribuidor,
            'id_estado_pedido'   => 1, // Estado inicial
            'fecha_creacion'       => now()->toDateString(),
            'hora_creacion'      => now()->toTimeString(),
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

        $this->despachorepository->createDespacho([
            'id_pedido' => $pedido->id,
            'direccion_entrega' => $usuario->direccion,
            'comuna' => $usuario->comuna,
            'fecha_entrega' => null,
            'persona_recibe' => $cotizacion->persona_recibe,
            'estado_despacho' => null,
        ]);

        return $pedido;
    }

    public function tomarcotizacionadmin($id_cotizacion, $id_usuario_dicreme){

        $usuario_dicreme = $this->usuariodicremeRepository->getUsuarioDicremeById($id_usuario_dicreme);
        $cotizacion = $this->cotizacionRepository->getCotizacionById($id_cotizacion);

        if(!$cotizacion){
            return false;
        }
        if(!$usuario_dicreme){
            return null;
        }

        if($cotizacion->id_usuario_dicreme != null){
            throw new \Exception("No puedes tomar esta cotizacion, ya tomada");
        }

        return $this->cotizacionRepository->TomarCotizacionUsuarioDicreme($id_cotizacion, $id_usuario_dicreme);
    }

    public function Dejarcotizacionadmin($id_cotizacion, $id_usuario_dicreme)
    {
        $cotizacion = $this->cotizacionRepository->getCotizacionById($id_cotizacion);
        $usuario = $this->usuariodicremeRepository->getUsuarioDicremeById($id_usuario_dicreme);

        if (!$cotizacion) {
            return null;
        }

        if (!$usuario){
            throw new \Exception("El usuario no existe");
        }

        if ($cotizacion->id_usuario_dicreme !== (int)$id_usuario_dicreme) {
            return false;
        }

        return $this->cotizacionRepository->DejarCotizacionUsuarioDicreme($id_cotizacion);
    }

    public function Cancelarcotizacionadmin($id_cotizacion, $id_usuario_dicreme)
    {
        $cotizacion = $this->cotizacionRepository->getCotizacionById($id_cotizacion);
        $usuario = $this->usuariodicremeRepository->getUsuarioDicremeById($id_usuario_dicreme);

        if (!$cotizacion) {
            return null;
        }

        if (!$usuario){
            throw new \Exception("El usuario no existe");
        }

        return $this->cotizacionRepository->CancelarCotizacion($id_cotizacion);
    }


    public function validarCotizacion($id_cotizacion, $id_usuario_dicreme){
        $cotizacion = $this->cotizacionRepository->getCotizacionById($id_cotizacion);
        $usuario = $this->usuariodicremeRepository->getUsuarioDicremeById($id_usuario_dicreme);

        if(!$usuario){
            throw new \Exception("El usuario no existe");
        }
        
        if ($cotizacion->id_usuario_dicreme !== (int)$id_usuario_dicreme) {
            return null;
        }

        if($cotizacion->id_estado_cotizacion == 2) { 

            return $this->cotizacionRepository->cotizacioncompletada($id_cotizacion);
        }
        
        if (!$cotizacion) {
            throw new \Exception("La cotizacion no existe");
        }

        return false;

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
