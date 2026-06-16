<?php

namespace App\Services;
use App\Repositories\DespachoRepository;
use App\Repositories\Usuario_dicremeRepository;
use App\Models\Despacho;

use function PHPUnit\Framework\throwException;

class DespachoServices
{
    protected $despachoRepository;
    protected $usuariodicremeRepository;

    public function __construct(DespachoRepository $despachoRepository, Usuario_dicremeRepository $usuariodicremeRepository)
    {
        $this->despachoRepository = $despachoRepository;
        $this->usuariodicremeRepository = $usuariodicremeRepository;
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
}
