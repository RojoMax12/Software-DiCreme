<?php

namespace App\Repositories;
use App\Models\Bodega;

# Repositorio Bodega
class BodegaRepository
{
    # Geters 
    public function getAllBodegas()
    {
        return Bodega::all();
    }

    public function getBodegaById($id)
    {
        return Bodega::find($id);
    }

    public function getBodegaByNombre($nombre)
    {
        return Bodega::where('nombre_bodega', $nombre)->first();
    }   

    public function getBodegaByUbicacion($ubicacion)
    {
        return Bodega::where('ubicacion_bodega', $ubicacion)->first();
    }

    public function getProductosByBodegaId($id)
    {
        $bodega = Bodega::find($id);
        if ($bodega) {
            return $bodega->productos; // Asumiendo que hay una relación definida en el modelo Bodega
        }
        return null;
    }

    # Seters
    # Pendientes hasta completar las relaciones entre los modelos.

}