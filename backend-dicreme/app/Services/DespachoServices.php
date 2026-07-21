<?php

namespace App\Services;
use App\Repositories\DespachoRepository;
use App\Repositories\Usuario_dicremeRepository;
use App\Repositories\Usuario_distribuidoresRepository;
use App\Repositories\PedidoRepository;
use Illuminate\Support\Facades\Mail;

class DespachoServices
{
    protected $despachoRepository;
    protected $usuariodicremeRepository;
    protected $usuariodistribuidorRepository;
    protected $pedidoRepository;

    public function __construct(
        DespachoRepository $despachoRepository,
        Usuario_dicremeRepository $usuariodicremeRepository,
        Usuario_distribuidoresRepository $usuariodistribuidorRepository,
        PedidoRepository $pedidoRepository
    ) {
        $this->despachoRepository = $despachoRepository;
        $this->usuariodicremeRepository = $usuariodicremeRepository;
        $this->usuariodistribuidorRepository = $usuariodistribuidorRepository;
        $this->pedidoRepository = $pedidoRepository;
    }

    public function getAllDespachos()
    {
        return $this->despachoRepository->getAllDespachos();
    }

    public function getDespachoById($id)
    {
        return $this->despachoRepository->getDespachoById($id);
    }

    public function createDespacho($data)
    {
        return $this->despachoRepository->createDespacho($data);
    }

    public function updateDespacho($id, $data)
    {
        return $this->despachoRepository->updateDespacho($id, $data);
    }

    public function deleteDespacho($id)
    {
        return $this->despachoRepository->deleteDespacho($id);
    }

    public function despachosbyidpedido($id){
        return $this->despachoRepository->getDespachoByIdpedido($id);
    }

    public function getDespachoByIdusuariodicreme($id){

        $despachador = $this->usuariodicremeRepository->getUsuarioDicremeById($id);

        if(!$despachador){
            return null;
        }

        if($despachador->id_rol !== 4){
            return false;
        }

        return $this->despachoRepository->getDespachoByIdUsuarioDiCreme($id);
    }


    public function getDespachosDisponibles()
    {
        return $this->despachoRepository->getDespachosDisponibles();
    }

    public function asignardespachoaldespachador($id_despacho, $id_despachador){

        $despacho = $this->despachoRepository->getDespachoById($id_despacho);
        $despachador = $this->usuariodicremeRepository->getUsuarioDicremeById($id_despachador);

        if(!$despacho) {
            return Null;
        }

        if(!$despachador || $despachador->id_rol !== 4){
            return false;
        }

        return $this->despachoRepository->asignardespachoundespachador($id_despacho, $id_despachador);
    }

    public function iniciarRuta($id_despacho)
    {
        $despacho = $this->despachoRepository->getDespachoById($id_despacho);
        if (!$despacho) {
            return null;
        }

        // 1. Cambiar estado despacho a 3 ("En ruta")
        $this->despachoRepository->updateDespacho($id_despacho, [
            'id_estado_despacho' => 3
        ]);

        // 2. Cambiar estado pedido a 4 ("En Despacho")
        if ($despacho->id_pedido) {
            $this->pedidoRepository->updatePedido($despacho->id_pedido, [
                'id_estado_pedido' => 4
            ]);
        }

        // 3. Enviar notificación por correo de forma automática al distribuidor
        $this->enviarNotificacionDespacho($id_despacho);

        return $this->despachoRepository->getDespachoById($id_despacho);
    }

    public function finalizarEntrega($id_despacho, $notasEntrega = null, $fotoFile = null)
    {
        $despacho = $this->despachoRepository->getDespachoById($id_despacho);
        if (!$despacho) {
            return null;
        }

        $fotoUrl = $despacho->foto_comprobante;

        // Manejar subida de archivo si existe
        if ($fotoFile) {
            $path = $fotoFile->store('comprobantes', 'public');
            $fotoUrl = '/storage/' . $path;
        }

        // 1. Actualizar despacho a estado 4 ("Entrega exitosa")
        $this->despachoRepository->updateDespacho($id_despacho, [
            'id_estado_despacho' => 4,
            'fecha_entrega' => \Carbon\Carbon::now('America/Santiago')->toDateTimeString(),
            'foto_comprobante' => $fotoUrl,
            'notas_entrega' => $notasEntrega
        ]);

        // 2. Actualizar pedido a estado 5 ("Entregado")
        if ($despacho->id_pedido) {
            $this->pedidoRepository->updatePedido($despacho->id_pedido, [
                'id_estado_pedido' => 5
            ]);
        }

        return $this->despachoRepository->getDespachoById($id_despacho);
    }

    // Metodos extras 

    // Función para enviar notificación de despacho a través de correo electrónico al distribuidor
    public function enviarNotificacionDespacho($id_despacho)
    {
        //Buscamos el despacho
        $despacho = $this->despachoRepository->getDespachoById($id_despacho);
        if (!$despacho) {
            return false;
        }

        //Buscamos el pedido asociado al despacho
        $pedido = $this->pedidoRepository->getPedidoById($despacho->id_pedido);
        if (!$pedido) {
            return false;
        }

        //Buscamos al distribuidor dueño de ese pedido
        $cliente = $this->usuariodistribuidorRepository->getUsuarioDistribuidorById($pedido->id_usuario_distribuidor);
        if (!$cliente || empty($cliente->correo_electronico)) {
            return false;
        }

        $id_pedido = $pedido->id;
        $correo_cliente = $cliente->correo_electronico;
        $urlFrontend = rtrim(config('app.frontend_url', 'http://localhost:5173'), '/') . '/pedido/' . $id_pedido;

        // Enviamos el correo con plantilla Blade
        try {
            Mail::send('emails.notificacion_despacho', [
                'url' => $urlFrontend,
                'despacho' => $despacho,
                'cliente' => $cliente
            ], function ($message) use ($correo_cliente) {
                $message->to($correo_cliente);
                $message->subject('Notificación de Despacho - DiCreme');
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo de despacho: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    public function liberarDespacho($id_despacho)
    {
        $despacho = $this->despachoRepository->getDespachoById($id_despacho);
        if (!$despacho) {
            return ['status' => 'error', 'code' => 404, 'message' => 'El despacho no existe'];
        }

        if ($despacho->id_estado_despacho != 1 && $despacho->id_estado_despacho != 2) {
            return ['status' => 'error', 'code' => 400, 'message' => 'Solo se pueden liberar despachos que estén en estado 1 (Pendiente) o 2 (Asignado)'];
        }

        $despachadorAnterior = $despacho->id_despachador;

        $this->despachoRepository->updateDespacho($id_despacho, [
            'id_despachador' => null,
            'id_estado_despacho' => 1
        ]);

        \App\Models\HistorialMovimiento::registrar(
            'usuario',
            $id_despacho,
            'liberacion',
            "Se liberó el despacho #{$id_despacho} devolviéndolo a pedidos disponibles",
            null,
            ['despachador_anterior' => $despachadorAnterior]
        );

        return ['status' => 'success', 'data' => $this->despachoRepository->getDespachoById($id_despacho)];
    }
}
