<?php

namespace App\Repositories;
use App\Models\Producto;

# Repositorio Producto
class ProductoRepository
{
    # Create
    public function createProducto($data)
    {
        return Producto::create($data);
    }

    # Geters
    public function getAllProductos()
    {
        return Producto::all();
    }

    public function getProductoById($id)
    {
        return Producto::find($id);
    }

    public function getProductoByNombre($nombre)
    {
        return Producto::where('nombre_producto', $nombre)->first();
    }

    public function getProductosByCategoriaId($idCategoria)
    {
        return Producto::where('id_categoria', $idCategoria)->get();
    }

    public function getProductosByFormatoId($idFormato)
    {
        return Producto::where('id_formato', $idFormato)->get();
    }

    # Seters
    public function updateProducto($id, $data)
    {
        $producto = Producto::find($id);
        if ($producto) {
            $producto->update($data);
            return $producto;
        }
        return null;
    }

    # Delete
    public function deleteProducto($id)
    {
        $producto = Producto::find($id);
        if ($producto) {
            $producto->delete();
            return true;
        }
        return false;
    }
}