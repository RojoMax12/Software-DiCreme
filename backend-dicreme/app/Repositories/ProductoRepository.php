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
            return Producto::leftJoin('categorias', 'productos.id_categoria', '=', 'categorias.id')
                ->leftJoin('formatos', 'productos.id_formato', '=', 'formatos.id')
                ->select(
                    'productos.id', 
                    'productos.nombre_producto', 
                    'productos.precio_producto', 
                    'productos.foto_producto',
                    'productos.estado_producto',
                    'productos.id_formato',
                    'productos.id_categoria',
                    'categorias.nombre_categoria',
                    'formatos.nombre_formato',
                    'formatos.precio_formato',
                    'formatos.imagen_formato'
                ) 
                ->get()
                ->map(function ($p) {
                    $p->estado_producto = (bool) $p->estado_producto;
                    return $p;
                })
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

    public function getProductosPocoStock($umbral = 10)
    {
        $productos = Producto::with(['formato', 'lotes'])->get();
        $umbralInt = (int)$umbral;

        $resumen = $productos->map(function ($producto) use ($umbralInt) {
            $cantidad_total = (int)$producto->lotes->sum('cantidad_producto');
            
            $statusText = '';
            $pillClass = '';
            $estadoStock = 'ok';

            if ($cantidad_total === 0) {
                $statusText = 'Sin stock';
                $pillClass = 'pill-orange';
                $estadoStock = 'sin_stock';
            } else if ($cantidad_total <= 5) {
                $statusText = "Faltan " . ($umbralInt - $cantidad_total) . " unidades";
                $pillClass = 'pill-red';
                $estadoStock = 'critico';
            } else if ($cantidad_total <= $umbralInt) {
                $statusText = "Pocas unidades (" . $cantidad_total . ")";
                $pillClass = 'pill-yellow';
                $estadoStock = 'bajo';
            }

            return [
                'id' => $producto->id,
                'nombre_producto' => $producto->nombre_producto,
                'formato' => $producto->formato,
                'name' => ($producto->nombre_producto ?? 'N/A') . ' - ' . ($producto->formato?->nombre_formato ?? 'N/A'),
                'cantidad_total' => $cantidad_total,
                'umbral_minimo' => $umbralInt,
                'unidades_faltantes' => max(0, $umbralInt - $cantidad_total),
                'estado_stock' => $estadoStock,
                'statusText' => $statusText,
                'pillClass' => $pillClass
            ];
        });

        return $resumen->filter(function ($item) use ($umbralInt) {
            return $item['cantidad_total'] <= $umbralInt;
        })->values();
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

    public function activarydesactivar($identifier)
    {
        $query = Producto::query();

        if (is_numeric($identifier)) {
            $query->where('id', '=', (int)$identifier)
                  ->orWhere('nombre_producto', '=', (string)$identifier);
        } else {
            $query->where('nombre_producto', '=', (string)$identifier);
        }

        $producto = $query->first();

        if ($producto) {
            $nuevoEstado = !(bool)$producto->estado_producto;

            // Actualizar todos los formatos asociados a este sabor
            Producto::where('nombre_producto', '=', $producto->nombre_producto)
                ->update(['estado_producto' => $nuevoEstado]);

            $producto->estado_producto = $nuevoEstado;
            $this->clearCache(); 
            return $producto; 
        }

        return null;
    }
}