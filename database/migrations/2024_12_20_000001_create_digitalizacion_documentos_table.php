
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('digitalizacion_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digitalizacione_id')->constrained('digitalizaciones')->onDelete('cascade');
            $table->string('nombre_archivo');
            $table->string('ruta');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('digitalizacion_documentos');
    }
};
