<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AltaDocumento extends Model
{
    use HasFactory;

    protected $table = 'alta_documentos';

    protected $fillable = [
        'alta_id', 'tipo_documento', 'archivo_ruta', 'estado_documento', 'motivo_rechazo'
    ];

    public function alta()
    {
        return $this->belongsTo(AltaCliente::class, 'alta_id');
    }
}