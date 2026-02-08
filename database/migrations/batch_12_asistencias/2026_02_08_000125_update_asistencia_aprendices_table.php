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
        Schema::table('asistencia_aprendices', function (Blueprint $table) {
            // Eliminar la relación directa con evidencias
            $table->dropForeign(['evidencia_id']);
            $table->dropColumn('evidencia_id');
            
            // Agregar la relación con asistencias
            $table->foreignId('asistencia_id')->nullable()->after('aprendiz_ficha_id')->constrained('asistencias')->onDelete('cascade');
            
            // Agregar índices para optimización
            $table->index(['asistencia_id', 'aprendiz_ficha_id']);
            $table->index(['asistencia_id', 'hora_ingreso']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencia_aprendices', function (Blueprint $table) {
            // Eliminar la relación con asistencias
            $table->dropForeign(['asistencia_id']);
            $table->dropColumn('asistencia_id');
            
            // Restaurar la relación con evidencias
            $table->foreignId('evidencia_id')->nullable()->after('aprendiz_ficha_id')->constrained('evidencias')->onDelete('cascade');
        });
    }
};
