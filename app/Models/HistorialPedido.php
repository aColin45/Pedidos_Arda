<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialPedido extends Model
{
    protected $table = 'historial_pedidos';
    
    protected $fillable = [
        'pedido_id', 
        'user_id', 
        'estado_anterior', 
        'estado_nuevo', 
        'comentario'
    ];

    public function pedido() {
        return $this->belongsTo(Pedido::class);
    }

    public function usuario() {
        return $this->belongsTo(User::class, 'user_id');
    }
}