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
        Schema::table('evidencias', function (Blueprint $table) {
            // Eliminar la restricción única del campo nombre para permitir duplicados
            $table->dropUnique('evidencias_nombre_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evidencias', function (Blueprint $table) {
            // Restaurar la restricción única si se necesita revertir
            $table->unique('nombre', 'evidencias_nombre_unique');
        });
    }
};
