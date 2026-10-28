<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialInventario extends Model
{
    protected $table = 'historial_inventarios';
    protected $fillable = ['user_id', 'accion', 'detalles'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}