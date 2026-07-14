<?php

namespace App\Services;

use App\Repositories\BodegaRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class BodegaServices
{
	protected $bodegaRepository;

    # Constructor de una instancia de BodegaRepository
	public function __construct(BodegaRepository $bodegaRepository)
	{
		$this->bodegaRepository = $bodegaRepository;
	}

    # Creators
	public function createBodega($data):mixed
	{	
		return DB::transaction(function () use ($data) {
         
            return $this->bodegaRepository->createBodega($data);
        });
	}

    # Geters 
	public function getAllBodegas()
	{
		return $this->bodegaRepository->getAllBodegas();
	}

	public function getBodegaById($id)
	{
		return $this->bodegaRepository->getBodegaById($id);
	}

	public function getBodegaByNombre($nombre)
	{
		return $this->bodegaRepository->getBodegaByNombre($nombre);
	}

	public function getBodegaByUbicacion($ubicacion)
	{
		return $this->bodegaRepository->getBodegaByUbicacion($ubicacion);
	}

	public function getProductosByBodegaId($id)
	{
		return $this->bodegaRepository->getProductosByBodegaId($id);
	}

    public function updateBodega($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            // Verificamos si existe antes de actualizar (opcional, dependiendo de tu repo)
            $bodega = $this->bodegaRepository->getBodegaById($id);
            if (!$bodega) {
                throw new Exception("La bodega con ID {$id} no existe.");
            }

            return $this->bodegaRepository->updateBodega($id, $data);
        });
    }

    # Delete
    public function deleteBodegaById($id)
    {
        return DB::transaction(function () use ($id) {
            $bodega = $this->bodegaRepository->getBodegaById($id);
            if (!$bodega) {
                throw new Exception("No se puede eliminar: la bodega no existe.");
            }

            // Aquí podrías agregar lógica extra, como verificar si la bodega tiene productos
            // antes de dejar que se elimine (para evitar dejar productos huérfanos).

            return $this->bodegaRepository->deleteBodegaById($id);
        });
    }
}
