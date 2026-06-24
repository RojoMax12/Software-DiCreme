<?php

namespace App\Repositories;
use App\Models\Producto;
use App\Models\Lote;
use App\Models\Formato;
use Illuminate\Support\Facades\Cache;

# Repositorio Producto
class ProductoRepository
{   
    private const CACHE_KEY = 'catalogo_completo_productos';

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
        // Si ya está en caché, lo devuelve en < 10ms. Si no, va a la DB.
        return Cache::remember(self::CACHE_KEY, now()->addHours(24), function () {
            // Traemos todos, pero optimizando la consulta al máximo
            return Producto::select('id', 'nombre_producto', 'precio_producto', 'id_categoria', 'id_formato') // 1. Solo campos necesarios
                ->with([
                    'categoria' => function($query) { $query->select('id', 'nombre_categoria'); }, // 2. Eager loading limpio
                    'formato'  => function($query) { $query->select('id', 'nombre_formato'); }
                ])
                ->get()
                ->values()
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
        // Usamos Eager Loading para evitar el problema de N+1 consultas
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
}