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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_pro', 60);
            $table->string('descripcion_pro', 150);
            $table->decimal('precio_pro',6,2);
            $table->boolean('disponibilidad_pro')->default(true);
            $table->string('imagenRef_pro', 60);
            
            $table->foreignId('id_categoria')
                    ->nullable()
                    ->constrained('categorias')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
