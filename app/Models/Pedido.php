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

    protected $fillable = ['user_id', 'cliente_id', 'total', 'estado', 'subtotal', 'descuento_aplicado', 'iva', 'comentarios', 'flete_pagado']; 
    
    protected $casts = [
        'flete_pagado' => 'boolean',
    ];

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
    // ACCESORS (COLUMNAS VIRTUALES - LÓGICA DE GUÍAS)
    // =========================================================

    /**
     * Obtiene la guía parcial buscando la etiqueta |GP:...| en comentarios.
     * Uso: $pedido->guia_parcial
     */
    public function getGuiaParcialAttribute()
    {
        preg_match('/\|GP:(.*?)\|/', $this->comentarios ?? '', $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Obtiene la guía completa buscando la etiqueta |GC:...| en comentarios.
     * Uso: $pedido->guia_completa
     */
    public function getGuiaCompletaAttribute()
    {
        preg_match('/\|GC:(.*?)\|/', $this->comentarios ?? '', $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Devuelve los comentarios SIN los códigos de guía para mostrarlos limpios al usuario.
     * Uso: $pedido->comentarios_limpios
     */
    public function getComentariosLimpiosAttribute()
    {
        $texto = $this->comentarios ?? '';
        $texto = preg_replace('/\|GP:(.*?)\|/', '', $texto); // Quitar etiqueta GP
        $texto = preg_replace('/\|GC:(.*?)\|/', '', $texto); // Quitar etiqueta GC
        return trim($texto);
    }
}