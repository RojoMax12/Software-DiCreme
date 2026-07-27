<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrusel extends Model
{
    use HasFactory;

    protected $table = 'carruseles';

    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen_url',
        'enlace',
        'orden',
        'estado'
    ];
}
