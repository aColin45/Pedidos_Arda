<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncuestaRespuesta extends Model
{
    protected $table = 'encuesta_respuestas';

    protected $fillable = [
        'user_id',
        'respuestas'
    ];

    // Le decimos a Laravel que el campo 'respuestas' es un arreglo JSON
    protected $casts = [
        'respuestas' => 'array',
    ];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}