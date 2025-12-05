<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero eliminar contactos duplicados, dejando solo el primero de cada proveedor
        $proveedoresConContactos = DB::table('proveedor_contactos')
            ->select('proveedor_id', DB::raw('MIN(id) as primer_id'))
            ->groupBy('proveedor_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($proveedoresConContactos as $proveedor) {
            DB::table('proveedor_contactos')
                ->where('proveedor_id', $proveedor->proveedor_id)
                ->where('id', '!=', $proveedor->primer_id)
                ->delete();
        }

        // Agregar restricción UNIQUE a proveedor_id para hacer la relación uno a uno
        Schema::table('proveedor_contactos', function (Blueprint $table) {
            $table->unique('proveedor_id', 'proveedor_contactos_proveedor_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedor_contactos', function (Blueprint $table) {
            $table->dropUnique('proveedor_contactos_proveedor_id_unique');
        });
    }
};
