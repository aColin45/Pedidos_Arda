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

    // Aquí irían tus relaciones si las tienes (ej. con PedidoDetalle)
    // public function detallesPedido() {
    //     return $this->hasMany(PedidoDetalle::class);
    // }
}