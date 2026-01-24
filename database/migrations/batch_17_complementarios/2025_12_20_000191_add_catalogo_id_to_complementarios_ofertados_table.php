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
        if (!Schema::hasTable('complementarios_ofertados')) {
            return;
        }

        Schema::table('complementarios_ofertados', function (Blueprint $table): void {
            if (!Schema::hasColumn('complementarios_ofertados', 'catalogo_id')) {
                $table->foreignId('catalogo_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('complementarios_catalogo')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('complementarios_ofertados')) {
            return;
        }

        Schema::table('complementarios_ofertados', function (Blueprint $table): void {
            if (Schema::hasColumn('complementarios_ofertados', 'catalogo_id')) {
                $table->dropForeign(['catalogo_id']);
                $table->dropColumn('catalogo_id');
            }
        });
    }
};


