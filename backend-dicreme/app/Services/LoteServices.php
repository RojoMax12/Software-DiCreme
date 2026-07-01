<?php

namespace App\Services;

use App\Repositories\LoteRepository;

class LoteServices
{
    protected $loteRepository;

    # Constructor de una instancia de LoteRepository
    public function __construct(LoteRepository $loteRepository)
    {
        $this->loteRepository = $loteRepository;
    }

    # Creators
    public function createLote(array $data)
{   
    // Asignamos la cantidad producida a la cantidad actual del producto
    // Usamos el operador ?? 0 para evitar errores si el campo viene vacío
    $data['cantidad_producto'] = $data['cantidad_producida'] ?? 0;
    
    return $this->loteRepository->createLote($data);
    }

    # Geters
    public function getAllLotes()
    {
        return $this->loteRepository->getAllLotes();
    }

    public function getLoteById($id)
    {
        return $this->loteRepository->getLoteById($id);
    }

    public function getLoteMasReciente()
    {
        return $this->loteRepository->getLoteMasReciente();
    }

    public function getLotesByProductoId($idProducto)
{
    $lotes = $this->loteRepository->getLotesByProductoId($idProducto);

    // Transformamos la colección de lotes
    $lotes->transform(function ($lote) {
        return [
            'id' => $lote->id,
            'id_producto' => $lote->id_producto,
            'id_bodega' => $lote->id_bodega,
            'cantidad_producida' => $lote->cantidad_producida,
            'cantidad_producto' => $lote->cantidad_producto,
            'fecha_vencimiento' => $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : null,
            'fecha_emision' => $lote->fecha_emision ? $lote->fecha_emision->format('d/m/Y') : null,
            'fecha_actualizacion' => $lote->updated_at ? $lote->updated_at->format('d/m/Y') : null,
            // Agregamos la bodega aquí mismo si la traes con 'with'
            'bodega' => $lote->bodega ? $lote->bodega->nombre_bodega : null 
        ];
    });

    return $lotes;
    }


    public function getLotesByStockId($idStock)
    {
        return $this->loteRepository->getLotesByStockId($idStock);
    }

    public function getLotesByBodegaId($idBodega)
    {
        return $this->loteRepository->getLotesByBodegaId($idBodega);
    }

    # Seters
    public function updateLote($id, $data)
    {
        return $this->loteRepository->updateLote($id, $data);
    }

    # Delete
    public function deleteLote($id)
    {
        return $this->loteRepository->deleteLote($id);
    }

    public function updateCantidadProducto($id, $cantidad)
    {
        return $this->loteRepository->updateLoteCantidadProducto($id, $cantidad);
    }

    
}