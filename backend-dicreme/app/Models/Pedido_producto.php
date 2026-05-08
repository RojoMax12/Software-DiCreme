<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido_producto extends Model
{
    use HasFactory;

    protected $table = 'pedido_producto';

    protected $fillable = [
        'id_pedido',
        'id_producto',
        'precio_unitario_venta',
        'cantidad'
    ];

}