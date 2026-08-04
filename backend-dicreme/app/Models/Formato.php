<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formato extends Model
{
    use HasFactory;

    protected $table = 'formatos';

    protected $fillable = [
        'nombre_formato',
        'precio_formato',
        'imagen_formato',
    ];

    protected $casts = [
        'precio_formato' => 'integer',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_formato');
    }

}