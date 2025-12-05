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
        // Migrar datos existentes del campo contacto a la nueva tabla proveedor_contactos
        $proveedores = DB::table('proveedores')
            ->whereNotNull('contacto')
            ->where('contacto', '!=', '')
            ->get();

        foreach ($proveedores as $proveedor) {
            DB::table('proveedor_contactos')->insert([
                'proveedor_id' => $proveedor->id,
                'nombre' => $proveedor->contacto,
                'telefono' => null,
                'email' => null,
                'user_create_id' => $proveedor->user_create_id,
                'user_update_id' => $proveedor->user_update_id,
                'created_at' => $proveedor->created_at,
                'updated_at' => $proveedor->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir: migrar contactos de vuelta al campo contacto
        // Solo se migra el primer contacto de cada proveedor
        $contactos = DB::table('proveedor_contactos')
            ->select('proveedor_id', 'nombre', 'user_create_id', 'user_update_id')
            ->orderBy('id')
            ->get()
            ->groupBy('proveedor_id');

        foreach ($contactos as $proveedorId => $contactosProveedor) {
            $primerContacto = $contactosProveedor->first();
            DB::table('proveedores')
                ->where('id', $proveedorId)
                ->update([
                    'contacto' => $primerContacto->nombre,
                ]);
        }
    }
};
