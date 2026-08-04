<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache; 
use App\Repositories\ProductoRepository; 

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'id_categoria',
        'id_formato',
        'nombre_producto',
        'precio_producto',
        'foto_producto',
        'estado_producto'
    ];

    protected $casts = [
        'precio_producto' => 'integer',
    ];

    /**
     * Eventos del modelo para limpiar el caché automáticamente
     */
    protected static function booted()
    {
        // Cuando un producto se crea o se edita
        static::saved(function () {
            Cache::forget(ProductoRepository::CACHE_KEY);
        });

        // Cuando un producto se elimina
        static::deleted(function () {
            Cache::forget(ProductoRepository::CACHE_KEY);
        });
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function formato(): BelongsTo
    {
        return $this->belongsTo(Formato::class, 'id_formato');
    }

    public function pedidoProductos(): HasMany
    {
        return $this->hasMany(Pedido_producto::class, 'id_producto');
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'id_producto');
    }

    public function cotizacionProductos(): HasMany
    {
        return $this->hasMany(Cotizacion_producto::class, 'id_producto');
    }
}
