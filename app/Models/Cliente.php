<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Pedido;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'codigo', 'contacto', 'telefono', 'email', 'direccion', 'estado', 'activo', 'user_id', 'descuento', 'monto_credito', 
        'dias_credito', 
        'fecha_otorgamiento', 
        'referencia_bancaria'];

    // Relación con el Agente de Ventas (User)
    public function agente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con Pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}