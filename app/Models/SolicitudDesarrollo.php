<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudDesarrollo extends Model
{
    use HasFactory;

    // Le indicamos explícitamente el nombre de la tabla
    protected $table = 'solicitudes_desarrollos';

    protected $fillable = [
        'user_id', 'tipo_solicitud', 'tipo_necesidad', 'descripcion',
        'justificacion', 'quien_solicita', 'linea_producto', 
        'producto_relacionado', 'cliente_referencia', 'estatus'
    ];

    // Relación: La solicitud fue creada por un Agente (User)
    public function agente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}