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
            $table->unsignedBigInteger('persona_id')->nullable()->after('estado_id')->comment('ID de la persona contacto del proveedor');

            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('set null');
            // No unique: una persona puede ser contacto de múltiples proveedores
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropColumn('persona_id');
        });
    }
};
