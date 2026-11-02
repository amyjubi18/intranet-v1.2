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
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('notification_type'); // 'perfil_incompleto', 'stock_consumibles', 'stock_oficina'
            $table->timestamp('read_at')->nullable();
            $table->string('hash')->nullable(); // Hash único para detectar cambios en las notificaciones
            $table->timestamps();

            // Un usuario solo puede tener una notificación no leída por tipo
            $table->unique(['user_id', 'notification_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};

