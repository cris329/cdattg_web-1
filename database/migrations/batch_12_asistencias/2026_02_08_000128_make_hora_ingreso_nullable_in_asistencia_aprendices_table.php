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
            $table->datetime('hora_ingreso')->nullable()->change();
            $table->datetime('hora_salida')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asistencia_aprendices', function (Blueprint $table) {
            $table->datetime('hora_ingreso')->nullable(false)->change();
            $table->datetime('hora_salida')->nullable(false)->change();
        });
    }
};
