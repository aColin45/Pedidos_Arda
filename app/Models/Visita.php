<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'prospecto_id', 'cliente_id', 'fecha_visita',
        'tipo_contacto', 'atendio_nombre', 'atendio_cargo',
        'lineas_rotacion', 'marcas_competencia', 'necesidades_detectadas',
        'objetivo_visita', 'resultado_visita', 'monto_generado',
        'acuerdos', 'fecha_proximo_seguimiento', 'observaciones', 'modo_contacto',
        'motivo_rechazo'
    ];

    // Formateo automático de fechas
    protected $casts = [
        'fecha_visita' => 'datetime',
        'fecha_proximo_seguimiento' => 'date',
    ];

    // Relación: Una visita fue realizada por un Agente
    public function agente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación: Una visita puede pertenecer a un Prospecto
    public function prospecto()
    {
        return $this->belongsTo(Prospecto::class, 'prospecto_id');
    }

    // Relación: Una visita puede pertenecer a un Cliente establecido
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}