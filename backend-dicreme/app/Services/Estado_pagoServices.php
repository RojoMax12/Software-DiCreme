<?php

namespace App\Services;
use App\Repositories\Estado_pagoRepository;

class Estado_pagoServices
{
    protected $estadoPagoRepository;

    public function __construct(Estado_pagoRepository $estadoPagoRepository)
    {
        $this->estadoPagoRepository = $estadoPagoRepository;
    }

    public function getAllEstadosPago()
    {
        return $this->estadoPagoRepository->getAllEstadosPago();
    }

    public function getEstadoPagoById($id)
    {
        return $this->estadoPagoRepository->getEstadoPagoById($id);
    }

    public function createEstadoPago($data)
    {
        return $this->estadoPagoRepository->createEstadoPago($data);
    }

    public function updateEstadoPago($id, $data)
    {
        return $this->estadoPagoRepository->updateEstadoPago($id, $data);
    }

    public function deleteEstadoPago($id)
    {
        return $this->estadoPagoRepository->deleteEstadoPago($id);
    }
}