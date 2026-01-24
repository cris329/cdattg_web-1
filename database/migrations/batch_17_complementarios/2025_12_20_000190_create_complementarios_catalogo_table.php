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
        if (Schema::hasTable('complementarios_catalogo')) {
            return;
        }

        Schema::create('complementarios_catalogo', function (Blueprint $table): void {
            $table->id();

            // Identificador oficial del programa (PRF_CODIGO)
            $table->string('prf_codigo', 20)->unique();

            // Versión actual almacenada (PRF_VERSION)
            $table->unsignedInteger('version')->default(1);

            // Código oficial completo (COD_VER), suele ser prf_codigo-version
            $table->string('cod_ver', 30)->nullable()->unique();

            // Datos básicos del programa
            $table->text('denominacion'); // PRF_DENOMINACION
            $table->string('nivel_formacion', 100); // NIVEL DE FORMACION (ej: "CURSO ESPECIAL")

            // Cantidad total de horas del programa (PRF_DURACION_MAXIMA)
            $table->unsignedInteger('duracion_horas');

            // Requisitos de ingreso (PRF_DESCRIPCION_REQUISITO)
            $table->text('requisitos_ingreso')->nullable();

            // Clasificación SENA (opcional pero útil para reportes y filtros)
            $table->string('linea_tecnologica', 200)->nullable(); // Linea Tecnológica
            $table->string('red_tecnologica', 200)->nullable(); // Red Tecnológica
            $table->string('red_conocimiento', 200)->nullable(); // Red de Conocimiento
            $table->string('modalidad', 100)->nullable(); // Modalidad (Presencial, Virtual, etc.)
            $table->string('apuesta_prioritaria', 200)->nullable(); // APUESTAS PRIORITARIAS
            $table->string('tipo_permiso', 200)->nullable(); // TIPO PERMISO

            // Flags derivados de valores Si/No en el catálogo
            $table->boolean('multiple_inscripcion')->default(false); // Multiple Inscripcion
            $table->boolean('alamedida')->default(false); // PRF_ALAMEDIDA
            $table->boolean('fic')->default(false); // FIC

            // Información adicional
            $table->unsignedInteger('creditos')->default(0); // PRF_CREDITOS
            $table->string('indice', 500)->nullable(); // Indice
            $table->string('ocupacion', 500)->nullable(); // Ocupación

            // Control simple para desactivar programas sin borrarlos
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Índices para consultas frecuentes
            $table->index('nivel_formacion');
            $table->index('modalidad');
            $table->index('linea_tecnologica');
            $table->index('red_tecnologica');
            $table->index('red_conocimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('complementarios_catalogo')) {
            return;
        }

        Schema::dropIfExists('complementarios_catalogo');
    }
};


