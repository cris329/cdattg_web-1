<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, eliminar la foreign key existente
        Schema::table('asistencia_aprendices', function (Blueprint $table) {
            $table->dropForeign(['aprendiz_ficha_id']);
        });

        // Actualizar los valores de aprendiz_ficha_id para que apunten directamente a aprendices.id
        // Esto asume que aprendiz_ficha_id en asistencia_aprendices contiene el ID de aprendiz_fichas_caracterizacion
        // Necesitamos obtener el aprendiz_id correspondiente de la tabla aprendiz_fichas_caracterizacion
        DB::statement('
            UPDATE asistencia_aprendices aa
            INNER JOIN aprendiz_fichas_caracterizacion afc ON aa.aprendiz_ficha_id = afc.id
            SET aa.aprendiz_ficha_id = afc.aprendiz_id
        ');

        // Crear la nueva foreign key que apunta a aprendices.id
        Schema::table('asistencia_aprendices', function (Blueprint $table) {
            $table->foreign('aprendiz_ficha_id')
                  ->references('id')
                  ->on('aprendices')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la foreign key que apunta a aprendices
        Schema::table('asistencia_aprendices', function (Blueprint $table) {
            $table->dropForeign(['aprendiz_ficha_id']);
        });

        // Revertir los valores (esto es complejo, ya que necesitaríamos mapear de vuelta)
        // Por simplicidad, dejamos los valores como están
        // En producción, deberías tener un backup antes de ejecutar esta migración

        // Restaurar la foreign key original
        Schema::table('asistencia_aprendices', function (Blueprint $table) {
            $table->foreign('aprendiz_ficha_id')
                  ->references('id')
                  ->on('aprendiz_fichas_caracterizacion')
                  ->onDelete('cascade');
        });
    }
};
