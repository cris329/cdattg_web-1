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
        if (!Schema::hasTable('instructor_ficha_resultados_aprendizaje')) {
            Schema::create('instructor_ficha_resultados_aprendizaje', function (Blueprint $table) {
                $table->id();
                $table->foreignId('instructor_ficha_id')
                      ->constrained('instructor_fichas_caracterizacion')
                      ->onDelete('cascade')
                      ->name('if_rap_instructor_ficha_id_fk');
                $table->foreignId('resultado_aprendizaje_id')
                      ->constrained('resultados_aprendizajes')
                      ->onDelete('cascade')
                      ->name('if_rap_resultado_id_fk');
                $table->timestamps();
                
                // Evitar duplicados: un mismo resultado de aprendizaje no puede estar asignado dos veces a la misma asignación
                $table->unique(['instructor_ficha_id', 'resultado_aprendizaje_id'], 'unique_instructor_ficha_resultado');
            });
        } else {
            // Si la tabla ya existe, intentar agregar las foreign keys
            try {
                Schema::table('instructor_ficha_resultados_aprendizaje', function (Blueprint $table) {
                    $table->foreign('instructor_ficha_id', 'if_rap_instructor_ficha_id_fk')
                          ->references('id')
                          ->on('instructor_fichas_caracterizacion')
                          ->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // La foreign key ya existe, ignorar
            }
            
            try {
                Schema::table('instructor_ficha_resultados_aprendizaje', function (Blueprint $table) {
                    $table->foreign('resultado_aprendizaje_id', 'if_rap_resultado_id_fk')
                          ->references('id')
                          ->on('resultados_aprendizajes')
                          ->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // La foreign key ya existe, ignorar
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_ficha_resultados_aprendizaje');
    }
};
