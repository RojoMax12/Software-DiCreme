<?php

namespace App\Services;
use App\Repositories\DespachoRepository;
use App\Repositories\Usuario_dicremeRepository;
use App\Repositories\Usuario_distribuidoresRepository;
use App\Repositories\PedidoRepository;
use App\Models\Despacho;
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

        //Enviamos el correo con plantilla Blade
        Mail::send('emails.notificacion_despacho', [
            'url' => $urlFrontend,
            'despacho' => $despacho,
            'cliente' => $cliente
        ], function ($message) use ($correo_cliente) {
            $message->to($correo_cliente);
            $message->subject('Notificación de Despacho - DiCreme');
        });

        return true;
    }
}
