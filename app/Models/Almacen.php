<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacenes';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];

    // Relación: Un almacén tiene muchos productos
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'almacen_producto')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }
}