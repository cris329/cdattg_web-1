<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Elimina el constraint unique de persona_id para permitir
     * que una persona sea contacto de múltiples proveedores.
     */
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            // Primero eliminar la foreign key
            $table->dropForeign(['persona_id']);
        });

        Schema::table('proveedores', function (Blueprint $table) {
            // Luego eliminar el índice único
            $table->dropUnique(['persona_id']);
        });

        Schema::table('proveedores', function (Blueprint $table) {
            // Finalmente recrear la foreign key sin unique
            $table->foreign('persona_id')
                ->references('id')
                ->on('personas')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            // Eliminar la foreign key
            $table->dropForeign(['persona_id']);
        });

        Schema::table('proveedores', function (Blueprint $table) {
            // Restaurar el constraint unique
            $table->unique('persona_id');
        });

        Schema::table('proveedores', function (Blueprint $table) {
            // Recrear la foreign key con unique
            $table->foreign('persona_id')
                ->references('id')
                ->on('personas')
                ->onDelete('set null');
        });
    }
};
