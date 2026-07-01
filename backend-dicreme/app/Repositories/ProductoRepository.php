<?php

namespace App\Repositories;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Formato;
use Illuminate\Support\Facades\Cache;

# Repositorio Producto
class ProductoRepository
{   
    public const CACHE_KEY = 'catalogo_completo_productos';

    # Create
    public function createProducto($data)
    {      
        $producto = Producto::create($data);
        $this->clearCache();
        return $producto;
    }

    # Geters
    public function getAllProductos()
{
    return Cache::remember(self::CACHE_KEY, now()->addHours(24), function () {
        return Producto::join('categorias', 'productos.id_categoria', '=', 'categorias.id')
            ->select(
                'productos.id', 
                'productos.nombre_producto', 
                'productos.precio_producto', 
                'productos.id_formato',
                'categorias.nombre_categoria' // Trae el nombre directamente aquí
            ) 
            ->get()
            ->toArray();
    });
}


    public function getCantidadTotalProductoFromAllLotes($id)
    {
        $producto = Producto::with('formato')->find($id);
        
        if (!$producto) {
            return null;
        }

        $nombre_producto = $producto->nombre_producto;
        $cantidad_total = $producto->lotes()->sum('cantidad_producto');
        $ultimo_lote = $producto->lotes()->orderBy('updated_at', 'desc')->first();

        return [
            'nombre_producto' => $nombre_producto,
            'cantidad_total' => $cantidad_total,
            'formato' => $producto->formato,
            'ultima_actualizacion_lote' => $ultimo_lote ? $ultimo_lote->updated_at->format('Y-m-d H:i:s') : null
        ];
    }

    public function getResumenTodosLosProductos()
    {
        $productos = Producto::with(['formato', 'lotes'])->get();
        
        return $productos->map(function ($producto) {
            $cantidad_total = $producto->lotes->sum('cantidad_producto');
            $ultimo_lote = $producto->lotes->sortByDesc('updated_at')->first();

            return [
                'id' => $producto->id,
                'nombre_producto' => $producto->nombre_producto,
                'cantidad_total' => $cantidad_total,
                'formato' => $producto->formato,
                'ultima_actualizacion_lote' => $ultimo_lote ? $ultimo_lote->updated_at->format('Y-m-d H:i:s') : null
            ];
        });
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
            $this->clearCache();
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
            $this->clearCache(); 
            return true;
        }
        return false;
    }

    private function clearCache()
    {
        Cache::forget(self::CACHE_KEY); // Adiós fotografía vieja
    }

    public function activarydesactivar($nombreExacto)
{
    $producto = Producto::where('nombre_producto', '=', $nombreExacto)->first();

    if ($producto) {
        $producto->estado_producto = !$producto->estado_producto;
        $producto->save();

        $this->clearCache(); 
        
        // Retornamos el estado actual para que el front lo sepa
        return $producto; 
    }

    return false;
}
}