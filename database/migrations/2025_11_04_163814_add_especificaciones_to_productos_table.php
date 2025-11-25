<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('productos', function (Blueprint $table) {
        // Lo ponemos después de 'descripcion' por orden
        $table->text('especificaciones')->nullable()->after('descripcion');
    });
}

public function down(): void
{
    Schema::table('productos', function (Blueprint $table) {
        $table->dropColumn('especificaciones');
    });
}
};
