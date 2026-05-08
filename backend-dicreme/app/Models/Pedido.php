<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'id_usuario_distribuidor',
        'id_estado_pedido',
        'id_usuario_dicreme',
        'fecha_creacion',
    ];

}