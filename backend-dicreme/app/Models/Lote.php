<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'id_producto',
        'id_stock',
        'id_bodega',
        'cantidad_producto',
        'fecha_vencimiento',
        'fecha_emision'
    ];

}
