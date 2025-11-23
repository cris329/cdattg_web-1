<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * IMPORTANT: Esta migración debe ejecutarse DESPUÉS de cambiar la foreign key
     * en asistencia_aprendices. Asegúrate de que la migración anterior se haya ejecutado.
     */
    public function up(): void
    {
        // Verificar que no haya foreign keys apuntando a esta tabla
        // La foreign key de asistencia_aprendices ya debería haber sido cambiada
        
        Schema::dropIfExists('aprendiz_fichas_caracterizacion');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear la tabla (por si necesitas revertir)
        Schema::create('aprendiz_fichas_caracterizacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aprendiz_id')->constrained('aprendices');
            $table->foreignId('ficha_id')->constrained('fichas_caracterizacion');
            $table->timestamps();
        });
    }
};
