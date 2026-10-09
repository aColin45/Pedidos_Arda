<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialAlta extends Model
{
    protected $table = 'historial_altas';
    protected $fillable = ['alta_cliente_id', 'user_id', 'accion', 'detalles'];

    // Para saber quién hizo el movimiento
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function alta()
    {
        return $this->belongsTo(AltaCliente::class, 'alta_cliente_id');
    }
}