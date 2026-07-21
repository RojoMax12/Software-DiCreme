<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialMovimiento extends Model
{
    use HasFactory;

    protected $table = 'historial_movimientos';

    protected $fillable = [
        'tipo_entidad',
        'id_entidad',
        'accion',
        'descripcion',
        'usuario_responsable',
        'detalles_json'
    ];

    protected $casts = [
        'detalles_json' => 'array',
    ];

    /**
     * Helper estático para registrar auditorías fácilmente
     */
    public static function registrar(
        string $tipoEntidad,
        ?int $idEntidad,
        string $accion,
        string $descripcion,
        ?string $usuario = null,
        ?array $detalles = null
    ): self {
        return self::create([
            'tipo_entidad'        => $tipoEntidad,
            'id_entidad'          => $idEntidad,
            'accion'              => $accion,
            'descripcion'         => $descripcion,
            'usuario_responsable' => $usuario ?? (auth()->user()?->nombre_usuario ?? auth()->user()?->nombre_empresa ?? 'Sistema'),
            'detalles_json'       => $detalles
        ]);
    }
}
