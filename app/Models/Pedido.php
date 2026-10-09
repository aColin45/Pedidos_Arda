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

    // QUITAMOS 'flete_pagado' de aquí para evitar errores si la columna no existe
    protected $fillable = ['user_id', 'cliente_id', 'total', 'estado', 'subtotal', 'descuento_aplicado', 'iva', 'comentarios', 'is_cotizacion', 'fecha_vencimiento', 'motivo_rechazo', 'validador_id', 'fecha_validacion', 'direccion_entrega']; 
    
    // QUITAMOS el cast de flete_pagado porque ahora es virtual
    protected $casts = [];

    public function detalles()
    {
        return $this->hasMany(PedidoDetalle::class);
    }
    
    public function agente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // =========================================================
    // ACCESORS (COLUMNAS VIRTUALES - SIN TOCAR BASE DE DATOS)
    // =========================================================

    /**
     * FLETE PAGADO (Virtual)
     * Busca la etiqueta |FP:1| en los comentarios.
     */
    public function getFletePagadoAttribute()
    {
        // Si encuentra |FP:1| devuelve true, si no, false.
        return str_contains($this->comentarios ?? '', '|FP:1|');
    }

    /**
     * Guía Parcial (Virtual)
     */
    public function getGuiaParcialAttribute()
    {
        preg_match('/\|GP:(.*?)\|/', $this->comentarios ?? '', $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Guía Completa (Virtual)
     */
    public function getGuiaCompletaAttribute()
    {
        preg_match('/\|GC:(.*?)\|/', $this->comentarios ?? '', $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Comentarios Limpios (Para mostrar al usuario sin códigos)
     */
    public function getComentariosLimpiosAttribute()
    {
        $texto = $this->comentarios ?? '';
        $texto = preg_replace('/\|GP:(.*?)\|/', '', $texto); // Quitar Guía P
        $texto = preg_replace('/\|GC:(.*?)\|/', '', $texto); // Quitar Guía C
        $texto = str_replace('|FP:1|', '', $texto);          // Quitar Flete
        return trim($texto);
    }

    public function validador()
    {
        return $this->belongsTo(User::class, 'validador_id');
    }

    public function historial()
    {
        return $this->hasMany(HistorialPedido::class, 'pedido_id')->orderBy('created_at', 'desc');
    }
}