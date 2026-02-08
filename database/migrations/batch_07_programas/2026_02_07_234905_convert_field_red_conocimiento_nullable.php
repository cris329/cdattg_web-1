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
        Schema::table('programas_formacion', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['red_conocimiento_id']);
            
            // Make the column nullable
            $table->foreignId('red_conocimiento_id')->nullable()->change();

            // Make horas_totales column nullable
            $table->integer('horas_totales')->nullable()->change();

            $table->integer('horas_etapa_lectiva')->nullable()->change();
            $table->integer('horas_etapa_productiva')->nullable()->change();
            
            // Re-add the foreign key constraint with onDelete('set null')
            $table->foreign('red_conocimiento_id')
                  ->references('id')
                  ->on('red_conocimientos')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programas_formacion', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['red_conocimiento_id']);
            
            // Make the column NOT nullable
            $table->foreignId('red_conocimiento_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint without onDelete('set null')
            $table->foreign('red_conocimiento_id')
                  ->references('id')
                  ->on('red_conocimientos');
        });
    }
};
