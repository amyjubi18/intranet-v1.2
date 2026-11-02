<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('digitalizacion_documentos', function (Blueprint $table) {
            $table->foreignId('tipo_documento_id')->nullable()->constrained('tipo_documentos')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('digitalizacion_documentos', function (Blueprint $table) {
            $table->dropForeign(['tipo_documento_id']);
            $table->dropColumn('tipo_documento_id');
        });
    }
};

