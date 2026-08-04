<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado_despacho extends Model
{
    use HasFactory;

    protected $table = 'estados_despacho';

    protected $fillable = [
        'nombre_estado'
    ];

    public function despachos()
    {
        return $this->hasMany(Despacho::class, 'id_estado_despacho');
    }
}