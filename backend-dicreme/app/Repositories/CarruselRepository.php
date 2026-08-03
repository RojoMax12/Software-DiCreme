<?php

namespace App\Repositories;
use App\Models\Carrusel;

# Repositorio Carrusel
class CarruselRepository
{
    # Create
    public function createCarrusel($data)
    {
        return Carrusel::create($data);
    }   

    # Getters
    public function getAllCarruseles($soloActivos = false)
    {
        $query = Carrusel::orderBy('orden', 'asc');
        
        if ($soloActivos) {
            $query->where('estado', true);
        }
        
        return $query->get();
    }   

    public function getCarruselById($id)
    {
        return Carrusel::find($id);
    }

    # Setters
    public function updateCarrusel($id, $data)
    {
        $carrusel = Carrusel::find($id);
        if ($carrusel) {   
            $carrusel->update($data);
            return $carrusel;
        }
        return null;
    }

    # Toggle Estado
    public function toggleEstadoCarrusel($id)
    {
        $carrusel = Carrusel::find($id);
        if ($carrusel) {
            $carrusel->estado = !$carrusel->estado;
            $carrusel->save();
            return $carrusel;
        }
        return null;
    }

    # Delete
    public function deleteCarrusel($id)
    {
        $carrusel = Carrusel::find($id);
        if ($carrusel) {
            $carrusel->delete();
            return true;
        }   
        return false;
    }
}
