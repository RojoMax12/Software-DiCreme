<?php

namespace App\Services;

use App\Repositories\CarruselRepository;

class CarruselService
{
    protected $carruselRepository;

    public function __construct(CarruselRepository $carruselRepository)
    {
        $this->carruselRepository = $carruselRepository;
    }

    public function createCarrusel($data)
    {
        return $this->carruselRepository->createCarrusel($data);
    }

    public function getAllCarruseles($soloActivos = false)
    {
        return $this->carruselRepository->getAllCarruseles($soloActivos);
    }

    public function getCarruselById($id)
    {
        return $this->carruselRepository->getCarruselById($id);
    }

    public function updateCarrusel($id, $data)
    {
        return $this->carruselRepository->updateCarrusel($id, $data);
    }

    public function deleteCarrusel($id)
    {
        return $this->carruselRepository->deleteCarrusel($id);
    }

    public function toggleEstadoCarrusel($id)
    {
        return $this->carruselRepository->toggleEstadoCarrusel($id);
    }
}
