<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AltaReferencia extends Model
{
    protected $table = 'alta_referencias';
    protected $fillable = ['alta_cliente_id', 'nombre', 'correo', 'telefono', 'estado_referencia', 'motivo_rechazo'];

    public function alta()
    {
        return $this->belongsTo(AltaCliente::class, 'alta_cliente_id');
    }
}