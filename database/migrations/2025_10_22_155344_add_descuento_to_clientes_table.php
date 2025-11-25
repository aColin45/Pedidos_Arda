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
    Schema::table('clientes', function (Blueprint $table) {
        $table->decimal('descuento', 5, 2)->default(0.00)->after('activo');
        // Usamos decimal(5, 2) para guardar el porcentaje (ej: 32.00, 40.00)
    });
}

public function down(): void
{
    Schema::table('clientes', function (Blueprint $table) {
        $table->dropColumn('descuento');
    });
}
};