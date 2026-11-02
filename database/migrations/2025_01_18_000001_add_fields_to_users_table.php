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
        Schema::table('users', function (Blueprint $table) {
            // Agregar campos adicionales para el registro de usuarios
            $table->string('username')->unique()->nullable()->after('id');
            $table->string('lastname')->nullable()->after('name');
            $table->string('cedula')->unique()->nullable()->after('lastname');
            $table->date('fecha_nacimiento')->nullable()->after('cedula');
            $table->string('departamento')->nullable()->after('email');
            $table->string('profile_photo')->nullable()->after('departamento');
            $table->boolean('status')->default(true)->after('profile_photo');
            $table->boolean('is_active')->default(true)->after('status');
            $table->string('telefono')->nullable();
            $table->text('direccion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'lastname',
                'cedula',
                'fecha_nacimiento',
                'departamento',
                'profile_photo',
                'status',
                'is_active',
                'telefono',
                'direccion',
            ]);
        });
    }
};

