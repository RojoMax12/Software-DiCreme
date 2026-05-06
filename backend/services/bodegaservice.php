<?php
namespace BACKEND\Services;

use BACKEND\repositories\bodegarepository;
use BACKEND\models\bodega;
use Exception;

class BodegaService {
    private $repo;

    public function __construct(BodegaRepository $repo) {
        $this->repo = $repo;
    }

 
    public function registrarBodega(array $datos) {
        
        if (strtoupper($datos['nombre']) === 'ADMIN') {
            throw new Exception("Nombre de bodega no permitido.");
        }

        // 2. Preparar el Modelo (Entidad)
        $bodega = new Bodega();
        $bodega->setNombre($datos['nombre']); // Aquí se sanitiza el dato [cite: 50]

        // 3. Persistencia mediante el Repository
        return $this->repo->crearBodega($bodega);
    }
}