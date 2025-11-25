<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Agregamos las 3 columnas que faltan
            // Las ponemos después de 'total' para que se vea ordenado
            
            $table->decimal('subtotal', 10, 2)->after('total');
            $table->decimal('descuento_aplicado', 10, 2)->after('subtotal');
            $table->decimal('iva', 10, 2)->after('descuento_aplicado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Quitamos las columnas en orden inverso
            $table->dropColumn('iva');
            $table->dropColumn('descuento_aplicado');
            $table->dropColumn('subtotal');
        });
    }
};