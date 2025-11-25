<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Añadimos la columna 'flete_pagado' como booleano
            // Por defecto será 'false' (no pagado)
            // La ponemos después de 'comentarios'
            $table->boolean('flete_pagado')->default(false)->after('comentarios');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('flete_pagado');
        });
    }
};
