<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Despacho extends Model
{
    use HasFactory;

    protected $table = 'despachos';
    
    protected $fillable = [
        'id_pedido',
        'direccion_entrega',
        'comuna',
        'fecha_entrega',
        'persona_recibe',
        'id_estado_despacho',
        'id_usuario_dicreme',
        'foto_comprobante',
        'notas_entrega'
    ];

    protected $casts = [
        'fecha_entrega' => 'datetime',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario_dicreme::class, 'id_usuario_dicreme');
    }

    public function estado_despacho(): BelongsTo
    {
        return $this->belongsTo(Estado_despacho::class, 'id_estado_despacho');
    }
}


