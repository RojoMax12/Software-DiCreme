<?php

namespace App\Repositories;
use App\Models\Lote;

# Repositorio Lote
class LoteRepository
{
    # Create
    public function createLote($data)
    {
        return Lote::create($data);
    }

    # Geters
    public function getAllLotes()
    {
        return Lote::all();
    }

    public function getLoteById($id)
    {
        return Lote::find($id);
    }

    public function getLoteMasReciente()
    {
        return Lote::orderBy('updated_at', 'desc')->first();
    }

    public function getLotesByProductoId($idProducto)
    {
        return Lote::with('bodega')
                ->where('id_producto', $idProducto)
                ->get();
    }

        public function getLotesDisponiblesByProductoId($idProducto)
    {
        return Lote::where('id_producto', $idProducto)
                ->where('cantidad_producto', '>', 0)
                ->orderBy('fecha_vencimiento', 'asc') // O 'created_at' para gastar el más viejo primero
                ->get();
    }

    public function getLotesDeProductos(array $idProductos)
    {
        return Lote::whereIn('id_producto', $idProductos)
                ->where('cantidad_producto', '>', 0)
                ->orderBy('fecha_vencimiento', 'asc') // O created_at
                ->get()
                ->groupBy('id_producto'); // Agrupa automáticamente por producto en PHP
    }

    public function getLotesByStockId($idStock)
    {
        return Lote::where('id_stock', $idStock)->get();
    }

    public function getLotesByBodegaId($idBodega)
    {
        return Lote::where('id_bodega', $idBodega)->get();
    }

    # Seters
    public function updateLote($id, $data)
    {
        $lote = Lote::find($id);
        if ($lote) {
            $lote->update($data);
            return $lote;
        }
        return null;
    }

    # Delete
    public function deleteLote($id)
    {
        $lote = Lote::find($id);
        if ($lote) {
            $lote->delete();
            return true;
        }
        return false;
    }

    public function updateLoteCantidadProducto($id, $cantidad)
    {
        $lote = Lote::find($id);
        if ($lote) {
            $lote->cantidad_producto = $cantidad;
            $lote->save();
            return $lote;
        }
        return null;
    }
}