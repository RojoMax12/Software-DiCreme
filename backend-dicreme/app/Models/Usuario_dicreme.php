<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario_dicreme extends Model
{
    use HasFactory;

    protected $table = 'usuarios_dicreme';

    protected $fillable = [
        'nombre_usuario',
        'correo_electronico',
        'contrasena',
        'id_rol'
    ];

}