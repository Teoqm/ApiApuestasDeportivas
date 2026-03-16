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
        Schema::create('apuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');  
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');  
            $table->enum('tipo_apuesta', ['local', 'empate', 'visitante']);
            $table->decimal('monto', 10, 2);
            $table->decimal('cuota', 5, 2);
            $table->decimal('ganancia', 10, 2);
            $table->enum('estado', ['activa', 'ganada', 'perdida', 'cobrada'])->default('activa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apuestas');
    }
};
