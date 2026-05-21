<?php

namespace App\Repositories;
use App\Models\Cotizacion;

class CotizacionRepository
{
    public function createCotizacion($data)
    {
        return Cotizacion::create($data);
    }

    public function getCotizacionById($id)
    {
        return Cotizacion::find($id);
    }

    public function updateCotizacion($id, $data)
    {
        $cotizacion = Cotizacion::find($id);
        if ($cotizacion) {
            $cotizacion->update($data);
            return $cotizacion;
        }
        return null;
    }

    public function deleteCotizacion($id)
    {
        $cotizacion = Cotizacion::find($id);
        if ($cotizacion) {
            $cotizacion->delete();
            return true;
        }
        return false;
    }
}