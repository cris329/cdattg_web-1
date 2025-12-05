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
        Schema::table('proveedores', function (Blueprint $table) {
            // Agregar índice único al campo NIT (permite múltiples NULL pero valores únicos)
            $table->unique('nit', 'proveedores_nit_unique');
            // Eliminar el campo contacto (ya normalizado en proveedor_contactos)
            $table->dropColumn('contacto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            // Restaurar el campo contacto
            $table->string('contacto', 100)->nullable()->after('municipio_id');
            // Eliminar restricción única del NIT
            $table->dropUnique('proveedores_nit_unique');
        });
    }
};
