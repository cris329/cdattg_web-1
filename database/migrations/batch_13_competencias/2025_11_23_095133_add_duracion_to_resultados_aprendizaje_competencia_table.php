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
        Schema::table('resultados_aprendizaje_competencia', function (Blueprint $table) {
            $table->decimal('duracion', 8, 2)->nullable()->after('competencia_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resultados_aprendizaje_competencia', function (Blueprint $table) {
            $table->dropColumn('duracion');
        });
    }
};
