<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aspirantes_complementarios', function (Blueprint $table) {
            if (!Schema::hasColumn('aspirantes_complementarios', 'documento_identidad_path')) {
                $table->string('documento_identidad_path')->nullable()->after('observaciones');
            }
            if (!Schema::hasColumn('aspirantes_complementarios', 'documento_identidad_nombre')) {
                $table->string('documento_identidad_nombre')->nullable()->after('documento_identidad_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aspirantes_complementarios', function (Blueprint $table) {
            if (Schema::hasColumn('aspirantes_complementarios', 'documento_identidad_nombre')) {
                $table->dropColumn('documento_identidad_nombre');
            }
            if (Schema::hasColumn('aspirantes_complementarios', 'documento_identidad_path')) {
                $table->dropColumn('documento_identidad_path');
            }
        });
    }
};
