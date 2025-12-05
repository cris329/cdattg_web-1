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
        Schema::create('proveedor_contactos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proveedor_id');
            $table->string('nombre', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->unsignedBigInteger('user_create_id');
            $table->unsignedBigInteger('user_update_id');
            $table->timestamps();

            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('cascade');
            $table->foreign('user_create_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('user_update_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedor_contactos');
    }
};
