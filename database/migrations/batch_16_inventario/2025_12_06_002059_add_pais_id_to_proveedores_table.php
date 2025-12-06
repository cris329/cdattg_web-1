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
            $table->unsignedBigInteger('pais_id')->nullable()->after('direccion')->comment('ID del país del proveedor');
            
            $table->foreign('pais_id')->references('id')->on('pais')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropForeign(['pais_id']);
            $table->dropColumn('pais_id');
        });
    }
};
