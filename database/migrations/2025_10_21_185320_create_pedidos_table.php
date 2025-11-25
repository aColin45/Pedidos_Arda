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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            // 1. Clave foránea al Agente (Usuario que hizo el pedido)
             $table->foreignId('user_id')
              ->constrained('users') // Apunta a la tabla 'users'
              ->onDelete('cascade'); 
        
            $table->decimal('total', 10, 2); 
            $table->string('estado', 20)->default('pendiente'); 
        
        // 2. Clave foránea al Cliente (Importante: debe ser el mismo tipo que clientes.id)
            $table->foreignId('cliente_id')
              ->constrained('clientes') // Apunta a la tabla 'clientes'
              ->onDelete('cascade');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
