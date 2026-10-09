<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestriccionInventario extends Model
{
    protected $table = 'restricciones_inventario';
    protected $fillable = ['palabra'];
}