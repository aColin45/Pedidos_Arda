<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AltaCliente extends Model
{
    use HasFactory;

    protected $table = 'altas_clientes';

    protected $fillable = [
        'user_id', 'tipo_alta', 'razon_social', 'rfc', 'regimen_fiscal', 'uso_cfdi',
        'contacto_pagos', 'email_pagos', 'telefono_pagos',
        'calle_fiscal', 'colonia_fiscal', 'municipio_fiscal', 'estado_fiscal', 'cp_fiscal', 'telefono_fiscal', 'email1_fiscal', 'email2_fiscal',
        'persona_recibe', 'calle_entrega', 'colonia_entrega', 'municipio_entrega', 'estado_entrega', 'cp_entrega',
        'referencias_comerciales', 'estado_alta', 'observaciones_generales',
        'monto_credito', 'dias_credito', 'fecha_otorgamiento', 'numero_cliente', 'referencia_bancaria', 'descuento'
    ];

    // Referencias comerciales
    protected $casts = [
        'referencias_comerciales' => 'array',
        'fecha_otorgamiento' => 'date',
    ];

    public function agente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documentos()
    {
        return $this->hasMany(AltaDocumento::class, 'alta_id');
    }

    // Relación para traer todo el historial ordenado del más reciente al más antiguo
    public function historial()
    {
        return $this->hasMany(HistorialAlta::class, 'alta_cliente_id')->orderBy('created_at', 'desc');
    }

    // Relación con las referencias individuales de la nueva tabla
    public function referencias()
    {
        return $this->hasMany(AltaReferencia::class, 'alta_cliente_id');
    }
}