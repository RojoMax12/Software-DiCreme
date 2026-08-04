<?php

namespace App\Services;

use App\Repositories\Estado_despachoRepository;

class Estado_despachoServices
{
    protected $estado_despachoRepository;

    public function __construct(Estado_despachoRepository $estado_despachoRepository)
    {
        $this->estado_despachoRepository = $estado_despachoRepository;
    }

    public function getAll()
    {
        return $this->estado_despachoRepository->getAll();
    }

    public function getById($id)
    {
        return $this->estado_despachoRepository->getById($id);
    }

    public function getByNombre($nombre)
    {
        return $this->estado_despachoRepository->getByNombre($nombre);
    }

    public function create($data)
    {
        return $this->estado_despachoRepository->create($data);
    }

    public function update($id, $data)
    {
        return $this->estado_despachoRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->estado_despachoRepository->delete($id);
    }
}