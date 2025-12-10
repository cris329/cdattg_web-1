<?php

namespace Database\Seeders;

use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\Tema;
use Illuminate\Database\Seeder;

class ComplementariosEstadosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Creando estados para programas complementarios...');

        // 1. Crear o actualizar el tema ESTADO_PROGRAMA_COMPLEMENTARIO
        $tema = $this->crearTema();

        // 2. Crear los parámetros de estado
        $parametros = $this->crearParametros();

        // 3. Asociar parámetros con el tema
        $this->asociarParametrosConTema($tema, $parametros);

        // 4. Actualizar programas existentes que tengan estado_id NULL
        $this->actualizarProgramasExistentes($parametros);

        $this->command->info('✅ Estados para programas complementarios creados exitosamente!');
    }

    /**
     * Crear o actualizar el tema ESTADO_PROGRAMA_COMPLEMENTARIO
     */
    private function crearTema(): Tema
    {
        $tema = Tema::updateOrCreate(
            ['name' => 'ESTADO_PROGRAMA_COMPLEMENTARIO'],
            [
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]
        );

        $this->command->info("   ✓ Tema '{$tema->name}' (ID: {$tema->id})");

        return $tema;
    }

    /**
     * Crear los parámetros de estado para programas complementarios
     */
    private function crearParametros(): array
    {
        $parametrosConfig = [
            ['id' => 277, 'name' => 'SIN OFERTA'],
            ['id' => 278, 'name' => 'CON OFERTA'],
            ['id' => 279, 'name' => 'CUPOS LLENOS'],
        ];

        $parametros = [];

        foreach ($parametrosConfig as $config) {
            $parametro = Parametro::updateOrCreate(
                ['id' => $config['id']],
                [
                    'name' => $config['name'],
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]
            );

            $this->command->info("   ✓ Parámetro '{$parametro->name}' (ID: {$parametro->id})");
            $parametros[] = $parametro;
        }

        return $parametros;
    }

    /**
     * Asociar parámetros con el tema
     */
    private function asociarParametrosConTema(Tema $tema, array $parametros): void
    {
        foreach ($parametros as $parametro) {
            ParametroTema::updateOrCreate(
                [
                    'tema_id' => $tema->id,
                    'parametro_id' => $parametro->id,
                ],
                [
                    'status' => 1,
                    'user_create_id' => null,
                    'user_edit_id' => null,
                ]
            );
        }

        $this->command->info("   ✓ {$tema->parametros()->count()} parámetros asociados al tema");
    }

    /**
     * Actualizar programas complementarios existentes que tengan estado_id NULL
     * Asignarles el estado "SIN OFERTA" por defecto
     */
    private function actualizarProgramasExistentes(array $parametros): void
    {
        // Buscar el parámetro "SIN OFERTA"
        $sinOfertaParametro = collect($parametros)->first(function ($parametro) {
            return $parametro->name === 'SIN OFERTA';
        });

        if (!$sinOfertaParametro) {
            $this->command->warn('   ⚠ No se encontró el parámetro "SIN OFERTA"');
            return;
        }

        // Buscar el ParametroTema correspondiente
        $parametroTema = ParametroTema::where('parametro_id', $sinOfertaParametro->id)
            ->whereHas('tema', function ($query) {
                $query->where('name', 'ESTADO_PROGRAMA_COMPLEMENTARIO');
            })
            ->first();

        if (!$parametroTema) {
            $this->command->warn('   ⚠ No se encontró la relación ParametroTema para "SIN OFERTA"');
            return;
        }

        // Actualizar programas con estado_id NULL
        $programas = \App\Models\Complementarios\ComplementarioOfertado::whereNull('estado_id')->get();
        
        if ($programas->count() > 0) {
            $actualizados = \App\Models\Complementarios\ComplementarioOfertado::whereNull('estado_id')
                ->update(['estado_id' => $parametroTema->id]);
            
            $this->command->info("   ✓ {$actualizados} programas actualizados con estado 'SIN OFERTA'");
        } else {
            $this->command->info('   ✓ No hay programas con estado_id NULL para actualizar');
        }
    }
}
