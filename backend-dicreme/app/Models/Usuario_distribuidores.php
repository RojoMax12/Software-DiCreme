<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario_distribuidores extends Model
{
    use HasFactory;

    protected $table = 'usuarios_distribuidores';

    protected $fillable = [
        'id_rol',
        'rut_empresa',
        'nombre_empresa',
        'contrasena',
        'direccion',
        'telefono',
        'correo_electronico',
        'comuna'
    ];

}