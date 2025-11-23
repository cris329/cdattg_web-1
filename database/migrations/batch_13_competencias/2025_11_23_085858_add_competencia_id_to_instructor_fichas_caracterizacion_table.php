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
        Schema::table('instructor_fichas_caracterizacion', function (Blueprint $table) {
            $table->foreignId('competencia_id')->nullable()->after('ficha_id')->constrained('competencias')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructor_fichas_caracterizacion', function (Blueprint $table) {
            $table->dropForeign(['competencia_id']);
            $table->dropColumn('competencia_id');
        });
    }
};
