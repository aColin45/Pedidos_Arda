<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prospecto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'razon_social', 'nombre_comercial', 'rfc', 'giro_negocio',
        'estado', 'municipio', 'ciudad', 'colonia', 'cp', 'calle_numero',
        'contacto_nombre', 'contacto_puesto', 'contacto_departamento', 
        'telefono', 'extension', 'celular', 'email',
        'origen_prospecto', 'fecha_primer_contacto', 'compra_similares',
        'marcas_actuales', 'proveedores_actuales', 'potencial_compra',
        'frecuencia_compra', 'condicion_compra', 'lineas_interes',
        'productos_especificos', 'cantidad_presentacion', 'estatus', 'motivo_descarte'
    ];

    // Le decimos a Laravel que maneje "lineas_interes" como un arreglo nativo
    protected $casts = [
        'lineas_interes' => 'array',
        'fecha_primer_contacto' => 'date',
    ];

    // Relación: Un prospecto pertenece a un agente (User)
    public function agente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}