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
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            
            // Relación con la evidencia
            $table->foreignId('evidencia_id')->nullable()->constrained('evidencias')->onDelete('cascade');
            
            // Relación con la ficha del instructor
            $table->foreignId('instructor_ficha_id')->nullable()->constrained('instructor_fichas_caracterizacion')->onDelete('cascade');
            
            // Datos de la sesión
            $table->date('fecha')->comment('Fecha de la sesión de asistencia');
            $table->datetime('hora_inicio')->comment('Hora de inicio de la sesión');
            $table->datetime('hora_fin')->nullable()->comment('Hora de finalización de la sesión');
            
            // Estado de la asistencia
            $table->boolean('is_finished')->default(false)->comment('Indica si la sesión está finalizada');
            
            // Auditoría
            $table->foreignId('user_create_id')->nullable()->constrained('users');
            $table->foreignId('user_edit_id')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Índices para optimización
            $table->index(['instructor_ficha_id', 'is_finished']);
            $table->index(['evidencia_id', 'is_finished']);
            $table->index(['fecha', 'is_finished']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
