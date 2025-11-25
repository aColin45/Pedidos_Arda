<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;
use App\Models\Pedido;   
use App\Models\Producto; 

class PedidoDetalle extends Model
{
    use HasFactory; 

    // AÑADIDOS: 'inner' y 'subtotal'
    protected $fillable = ['pedido_id', 'producto_id', 'cantidad', 'precio', 'inner', 'subtotal'];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
    
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}