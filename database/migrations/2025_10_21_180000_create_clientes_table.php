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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('contacto', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('direccion')->nullable();
            $table->boolean('activo')->default(true);
            
            // Columna de asignación al agente de ventas (Usuario)
            // NO debe ser nullable, cada cliente DEBE pertenecer a un agente.
            $table->foreignId('user_id')
                  ->constrained('users') // Apunta a la tabla 'users'
                  ->onDelete('cascade'); // Si se borra el agente, se borran sus clientes.
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};