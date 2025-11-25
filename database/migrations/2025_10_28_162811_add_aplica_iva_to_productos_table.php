<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Columna booleana para indicar si el producto lleva IVA
            // Por defecto, asumimos que SÍ lleva IVA (true)
            // La ponemos después de 'precio' por orden lógico
            $table->boolean('aplica_iva')->default(true)->after('precio');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('aplica_iva');
        });
    }
};
