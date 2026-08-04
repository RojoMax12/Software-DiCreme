<?php

namespace App\Repositories;

use App\Models\Estado_despacho;

class Estado_despachoRepository
{
    public function getAll()
    {
        return Estado_despacho::all();
    }

    public function getById($id)
    {
        return Estado_despacho::find($id);
    }

    public function getByNombre($nombre)
    {
        return Estado_despacho::where('nombre_estado', $nombre)->first();
    }

    public function create($data)
    {
        return Estado_despacho::create($data);
    }

    public function update($id, $data)
    {
        $estado_despacho = Estado_despacho::find($id);
        if ($estado_despacho) {
            $estado_despacho->update($data);
            return $estado_despacho;
        }
        return null;
    }

    public function delete($id)
    {
        $estado_despacho = Estado_despacho::find($id);
        if ($estado_despacho) {
            $estado_despacho->delete();
            return true;
        }
        return false;
    }
}
