<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Elimina modalidad_id de complementarios_ofertados ya que ahora
     * se obtiene desde complementarios_catalogo a través de catalogo_id.
     */
    public function up(): void
    {
        if (!Schema::hasTable('complementarios_ofertados')) {
            return;
        }

        Schema::table('complementarios_ofertados', function (Blueprint $table) {
            if (Schema::hasColumn('complementarios_ofertados', 'modalidad_id')) {
                $table->dropForeign(['modalidad_id']);
                $table->dropColumn('modalidad_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('complementarios_ofertados')) {
            return;
        }

        Schema::table('complementarios_ofertados', function (Blueprint $table) {
            if (!Schema::hasColumn('complementarios_ofertados', 'modalidad_id')) {
                $table->foreignId('modalidad_id')
                    ->nullable()
                    ->after('estado_id')
                    ->constrained('parametros_temas')
                    ->onDelete('set null');
            }
        });
    }
};
