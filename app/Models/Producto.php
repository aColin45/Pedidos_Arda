<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory; 

    protected $fillable = [
        'codigo',
        'nombre',
        'precio',
        'aplica_iva',
        'descripcion',
        'especificaciones',
        'imagen',
        'inner',
    ];

    protected $casts = [
        'aplica_iva' => 'boolean',
    ];

    // --- NUEVA RELACIÓN PARA PRODUCTOS ESPECIALES ---
    // Relación: Un producto especial puede ser visto por varios usuarios
    public function usuariosPermitidos()
    {
        return $this->belongsToMany(User::class, 'producto_user', 'producto_id', 'user_id');
    }

    // Relación: Un producto puede estar en varios almacenes
    public function almacenes()
    {
        return $this->belongsToMany(Almacen::class, 'almacen_producto')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }
    
    // Función de ayuda para sumar todo el stock disponible
    public function getStockTotalAttribute()
    {
        return $this->almacenes()->sum('almacen_producto.cantidad');
    }
}