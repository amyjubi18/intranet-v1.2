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
        Schema::create('distribucion_almacenamiento_impresoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacenamiento_impresora_id')->constrained('almacenamiento_impresoras')->onDelete('cascade')->name('distribucion_almacenamiento_impresora_id_foreign');
            $table->foreignId('gerencia_id')->constrained('gerencias')->onDelete('cascade')->name('distribucion_gerencia_id_foreign');
            $table->integer('unidades_asignadas');
            $table->date('fecha_asignacion')->default(now());
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribucion_almacenamiento_impresoras');
    }
};
