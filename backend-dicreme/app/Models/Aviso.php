<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    use HasFactory;

    protected $table = 'avisos';

    protected $fillable = [
        'mensaje',
        'orden',
        'estado'
    ];
}
