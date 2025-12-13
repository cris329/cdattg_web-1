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
        Schema::table('personas', function (Blueprint $table) {
            $table->unsignedBigInteger('nivel_escolaridad_id')->nullable()->after('parametro_id');
            $table->foreign('nivel_escolaridad_id')->references('id')->on('parametros_temas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign(['nivel_escolaridad_id']);
            $table->dropColumn('nivel_escolaridad_id');
        });
    }
};
