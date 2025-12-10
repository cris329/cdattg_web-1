<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Nota: Los temas y parámetros de Sofia se crean en el TemaSeeder,
     * no en las migraciones. Las migraciones solo deben manejar estructura.
     */
    public function up(): void
    {
        // Esta migración no crea datos, solo estructura si es necesaria
        // Los temas y parámetros de Sofia se crean en TemaSeeder
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay estructura que eliminar
    }
};

