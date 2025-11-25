<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Añadimos 'codigo' después de 'nombre'
            // Lo hacemos 'unique' para que no se repita
            // Lo hacemos 'nullable' por si quieres asignarlo después o no es obligatorio
            $table->string('codigo', 50)->unique()->nullable()->after('nombre'); 
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Importante: Eliminar el índice único antes de borrar la columna
            $table->dropUnique(['codigo']); 
            $table->dropColumn('codigo');
        });
    }
};
