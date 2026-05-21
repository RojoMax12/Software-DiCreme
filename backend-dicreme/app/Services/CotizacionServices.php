<?php

namespace App\Services;
use App\Repositories\CotizacionRepository;

class CotizacionServices
{
    protected $cotizacionRepository;

    public function __construct(CotizacionRepository $cotizacionRepository)
    {
        $this->cotizacionRepository = $cotizacionRepository;
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
}