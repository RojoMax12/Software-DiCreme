<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'id_pedido',
        'numero_factura',
        'fecha_venta',
        'glosa',
        'estado_pago',
        'monto_total'
    ];

}