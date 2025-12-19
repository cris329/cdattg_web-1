<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Elimina campos duplicados de complementarios_ofertados que ya existen
     * en complementarios_catalogo. Estos datos deben obtenerse a través
     * de la relación con el catálogo:
     * - nombre -> catalogo.denominacion
     * - duracion -> catalogo.duracion_horas
     * - requisitos_ingreso -> catalogo.requisitos_ingreso
     */
    public function up(): void
    {
        if (!Schema::hasTable('complementarios_ofertados')) {
            return;
        }

        Schema::table('complementarios_ofertados', function (Blueprint $table) {
            // Eliminar campos duplicados que están en el catálogo
            if (Schema::hasColumn('complementarios_ofertados', 'nombre')) {
                $table->dropColumn('nombre');
            }
            
            if (Schema::hasColumn('complementarios_ofertados', 'duracion')) {
                $table->dropColumn('duracion');
            }
            
            if (Schema::hasColumn('complementarios_ofertados', 'requisitos_ingreso')) {
                $table->dropColumn('requisitos_ingreso');
            }
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Restaura los campos eliminados para permitir rollback.
     */
    public function down(): void
    {
        if (!Schema::hasTable('complementarios_ofertados')) {
            return;
        }

        Schema::table('complementarios_ofertados', function (Blueprint $table) {
            // Restaurar campos con sus tipos originales
            if (!Schema::hasColumn('complementarios_ofertados', 'nombre')) {
                $table->string('nombre')->after('codigo');
            }
            
            if (!Schema::hasColumn('complementarios_ofertados', 'duracion')) {
                $table->integer('duracion')->after('justificacion');
            }
            
            if (!Schema::hasColumn('complementarios_ofertados', 'requisitos_ingreso')) {
                $table->text('requisitos_ingreso')->nullable()->after('justificacion');
            }
        });
    }
};
