<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Verificar si la columna existe antes de eliminarla
        $columnExists = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM `digitalizaciones` LIKE 'tipo_documento_id'");

        if (!empty($columnExists)) {
            Schema::table('digitalizaciones', function (Blueprint $table) {
                $table->dropColumn('tipo_documento_id');
            });
        }
    }

    public function down()
    {
        Schema::table('digitalizaciones', function (Blueprint $table) {
            $table->foreignId('tipo_documento_id')->nullable()->constrained('tipo_documentos')->onDelete('set null');
        });
    }
};

