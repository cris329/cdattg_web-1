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
        Schema::table('complementarios_ofertados', function (Blueprint $table) {
            if (!Schema::hasColumn('complementarios_ofertados', 'ambiente_comentario')) {
                $table->text('ambiente_comentario')->nullable()->after('ambiente_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complementarios_ofertados', function (Blueprint $table) {
            if (Schema::hasColumn('complementarios_ofertados', 'ambiente_comentario')) {
                $table->dropColumn('ambiente_comentario');
            }
        });
    }
};
