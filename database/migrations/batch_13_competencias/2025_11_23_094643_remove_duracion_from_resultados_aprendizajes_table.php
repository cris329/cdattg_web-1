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
        Schema::table('resultados_aprendizajes', function (Blueprint $table) {
            $table->dropColumn('duracion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resultados_aprendizajes', function (Blueprint $table) {
            $table->float('duracion', 8, 2)->nullable(false)->after('nombre');
        });
    }
};
