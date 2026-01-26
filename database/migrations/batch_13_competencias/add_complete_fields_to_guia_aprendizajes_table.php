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
        Schema::table('guia_aprendizajes', function (Blueprint $table) {
            // Agregar campos faltantes para completar el modelo
            if (!Schema::hasColumn('guia_aprendizajes', 'programa_formacion_id')) {
                $table->foreignId('programa_formacion_id')->nullable()->after('descripcion')->constrained('programas_formacion');
            }
            if (!Schema::hasColumn('guia_aprendizajes', 'duracion_meses')) {
                $table->integer('duracion_meses')->nullable()->after('duracion_horas');
            }
            if (!Schema::hasColumn('guia_aprendizajes', 'objetivo_general')) {
                $table->text('objetivo_general')->nullable()->after('duracion_meses');
            }
            if (!Schema::hasColumn('guia_aprendizajes', 'metodologia')) {
                $table->text('metodologia')->nullable()->after('objetivo_general');
            }
            if (!Schema::hasColumn('guia_aprendizajes', 'evaluacion')) {
                $table->text('evaluacion')->nullable()->after('metodologia');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guia_aprendizajes', function (Blueprint $table) {
            // Eliminar columnas agregadas
            if (Schema::hasColumn('guia_aprendizajes', 'programa_formacion_id')) {
                $table->dropForeign(['programa_formacion_id']);
                $table->dropColumn('programa_formacion_id');
            }
            if (Schema::hasColumn('guia_aprendizajes', 'duracion_meses')) {
                $table->dropColumn('duracion_meses');
            }
            if (Schema::hasColumn('guia_aprendizajes', 'objetivo_general')) {
                $table->dropColumn('objetivo_general');
            }
            if (Schema::hasColumn('guia_aprendizajes', 'metodologia')) {
                $table->dropColumn('metodologia');
            }
            if (Schema::hasColumn('guia_aprendizajes', 'evaluacion')) {
                $table->dropColumn('evaluacion');
            }
        });
    }
};
