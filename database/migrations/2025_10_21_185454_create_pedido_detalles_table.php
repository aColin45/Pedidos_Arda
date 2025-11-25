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
        Schema::create('pedido_detalles', function (Blueprint $table) {
            $table->id();

            // Relación con el pedido
            $table->foreignId('pedido_id')
                  ->constrained('pedidos')
                  ->onDelete('cascade');
            
            // Relación con el producto
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->onDelete('cascade'); // Si el producto se borra, el detalle también

            // Campos del detalle
            $table->integer('cantidad');
            $table->decimal('precio', 6, 2); // Precio unitario al momento de la compra
            $table->unsignedSmallInteger('inner')->default(1); 
            $table->decimal('subtotal', 10, 2); // (Cantidad * Precio)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_detalles');
    }
};