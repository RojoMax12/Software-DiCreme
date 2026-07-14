<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Estado_pago extends Model
{
    use HasFactory;

    protected $table = 'estados_pago';

    protected $fillable = [
        'nombre_estado'
    ];

    public function pedidos(): HasOne
    {
        return $this->hasOne(Pedido::class, 'id_estado_pago');
    }

}