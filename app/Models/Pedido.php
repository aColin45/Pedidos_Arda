<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;
use App\Models\User;           
use App\Models\Cliente;        
use App\Models\PedidoDetalle; 

class Pedido extends Model
{
    use HasFactory; 

    // AÑADIR 'cliente_id' al fillable.
    protected $fillable = ['user_id', 'cliente_id', 'total', 'estado', 'subtotal', 'descuento_aplicado', 'iva', 'comentarios', 'flete_pagado']; 
    
    // Nuevo: Cast para que Laravel trate 'flete_pagado' como booleano (true/false)
    protected $casts = [
        'flete_pagado' => 'boolean',
    ];

    public function detalles()
    {
        return $this->hasMany(PedidoDetalle::class);
    }
    
    // Relación con el Agente que creó el pedido (user_id)
    // Renombrado de 'user' a 'agente' por consistencia
    public function agente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // NUEVA RELACIÓN con el Cliente final
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}