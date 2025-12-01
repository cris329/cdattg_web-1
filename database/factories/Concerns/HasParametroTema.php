<?php

declare(strict_types=1);

namespace Database\Factories\Concerns;

use App\Exceptions\InventarioFactoryException;
use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\Tema;

trait HasParametroTema
{
    /**
     * Obtiene un parametro_tema aleatorio o crea uno básico si no existe ninguno
     */
    protected function obtenerParametroTemaAleatorio(): int
    {
        $parametroTemaId = $this->obtenerParametroTemaExistente();
        if ($parametroTemaId !== null) {
            return $parametroTemaId;
        }

        return $this->crearParametroTemaBasico();
    }

    /**
     * Retorna la clase de excepción a usar. Puede ser sobrescrita por factories específicos.
     */
    protected function getParametroTemaExceptionClass(): string
    {
        return InventarioFactoryException::class;
    }

    private function obtenerParametroTemaExistente(): ?int
    {
        try {
            return ParametroTema::query()->inRandomOrder()->value('id');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function crearParametroTemaBasico(): int
    {
        try {
            $tema = $this->obtenerOCrearTema();
            $parametro = $this->obtenerOCrearParametro();
            $parametroTema = $this->crearParametroTema($tema, $parametro);

            if ($parametroTema !== null) {
                return $parametroTema->id;
            }
        } catch (\Throwable $e) {
            $exceptionClass = $this->getParametroTemaExceptionClass();
            throw new $exceptionClass(
                'No se encontró ningún parametro_tema y no se pudo crear uno. ' .
                'Error: ' . $e->getMessage() . '. ' .
                'Ejecuta los seeders necesarios (TemaSeeder, ParametroSeeder).',
                0,
                $e
            );
        }

        $exceptionClass = $this->getParametroTemaExceptionClass();
        throw new $exceptionClass(
            'No se encontró ningún parametro_tema y no se pudo crear uno. ' .
            'Ejecuta los seeders necesarios (TemaSeeder, ParametroSeeder).'
        );
    }

    private function obtenerOCrearTema(): Tema
    {
        $tema = Tema::query()->inRandomOrder()->first();

        if ($tema === null) {
            $tema = Tema::query()->create([
                'name' => 'TEMA FACTORY ' . uniqid(),
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]);
        }

        return $tema;
    }

    private function obtenerOCrearParametro(): Parametro
    {
        $parametro = Parametro::query()->inRandomOrder()->first();

        if ($parametro === null) {
            $parametro = Parametro::query()->create([
                'name' => 'PARAMETRO FACTORY ' . uniqid(),
                'status' => 1,
                'user_create_id' => null,
                'user_edit_id' => null,
            ]);
        }

        return $parametro;
    }

    private function crearParametroTema(Tema $tema, Parametro $parametro): ?ParametroTema
    {
        $tema->parametros()->syncWithoutDetaching([
            $parametro->id => ['status' => 1]
        ]);

        return ParametroTema::query()
            ->where('tema_id', $tema->id)
            ->where('parametro_id', $parametro->id)
            ->orderBy('id', 'desc')
            ->first();
    }
}

