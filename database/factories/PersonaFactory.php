<?php

namespace Database\Factories;

use App\Exceptions\DatabaseTableNotFoundException;
use App\Exceptions\ParametroTemaCreationException;
use App\Models\Parametro;
use App\Models\ParametroTema;
use App\Models\Persona;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Persona>
 */
class PersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener un usuario existente o usar ID por defecto para evitar dependencia circular
        $userId = User::query()->inRandomOrder()->value('id') ?? config('app.audit_default_user_id', 1);

        $generoParametroId = [9, 10, 11][array_rand([9, 10, 11])];
        $generoParametroTemaId = $this->obtenerOCrearParametroTema(3, $generoParametroId, $userId); // Tema: GENERO (3)

        // Validar que se obtuvo un ID válido y que existe en parametros_temas
        if (! $generoParametroTemaId) {
            throw new ParametroTemaCreationException(3, $generoParametroId, 'No se pudo obtener o crear el parametro_tema para genero');
        }

        // Verificar que el ID realmente existe en parametros_temas y tiene el tema_id y parametro_id correctos
        $verificacionGenero = ParametroTema::query()->find($generoParametroTemaId);
        if (! $verificacionGenero || $verificacionGenero->tema_id !== 3 || $verificacionGenero->parametro_id !== $generoParametroId) {
            throw new ParametroTemaCreationException(3, $generoParametroId, "El ID devuelto ({$generoParametroTemaId}) no es válido para genero. Tema_id: " . ($verificacionGenero->tema_id ?? 'null') . ", Parametro_id: " . ($verificacionGenero->parametro_id ?? 'null'));
        }

        $nombresMasculinos = ['Carlos', 'Juan', 'Pedro', 'Luis', 'Miguel', 'José', 'Andrés', 'Jorge', 'Diego', 'Fernando'];
        $nombresFemeninos = ['María', 'Ana', 'Carmen', 'Laura', 'Sofía', 'Valentina', 'Lucía', 'Isabella', 'Camila', 'Daniela'];
        $nombresNeutros = ['Alex', 'Taylor', 'Jordan', 'Casey', 'Morgan', 'Riley', 'Skyler', 'Avery', 'Quinn', 'Jamie'];

        $primerNombre = match ($generoParametroId) {
            9 => $nombresMasculinos[array_rand($nombresMasculinos)],
            10 => $nombresFemeninos[array_rand($nombresFemeninos)],
            default => $nombresNeutros[array_rand($nombresNeutros)],
        };

        $apellidos = ['García', 'Rodríguez', 'González', 'Fernández', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Gómez', 'Ramírez', 'Torres', 'Flores', 'Rivera', 'Silva', 'Morales'];

        // Obtener ubicación válida o usar valores por defecto
        $ubicacion = $this->obtenerUbicacionValida();

        $numeroDocumento = str_pad(rand(100000000, 9999999999), 10, '0', STR_PAD_LEFT);
        $timestamp = time();
        $email = strtolower($primerNombre) . rand(1000, 9999) . '@example.com';

        $segundoNombre = null;
        if (rand(1, 100) <= 50) {
            $segundoNombre = $generoParametroId == 9
                ? $nombresMasculinos[array_rand($nombresMasculinos)]
                : $nombresFemeninos[array_rand($nombresFemeninos)];
        }

        $segundoApellido = (rand(1, 100) <= 50) ? $apellidos[array_rand($apellidos)] : null;

        // Obtener tipo_documento de parametros_temas
        $tiposDocumentoParametroIds = [3, 4, 5, 6]; // CÉDULA, EXTRANJERÍA, PASAPORTE, TARJETA
        $tipoDocumentoParametroId = $tiposDocumentoParametroIds[array_rand($tiposDocumentoParametroIds)];
        $tipoDocumentoParametroTemaId = $this->obtenerOCrearParametroTema(2, $tipoDocumentoParametroId, $userId); // Tema: TIPO DE DOCUMENTO (2)

        // Validar que se obtuvo un ID válido y que existe en parametros_temas
        if (! $tipoDocumentoParametroTemaId) {
            throw new ParametroTemaCreationException(2, $tipoDocumentoParametroId, 'No se pudo obtener o crear el parametro_tema para tipo_documento');
        }

        // Verificar que el ID realmente existe en parametros_temas y tiene el tema_id y parametro_id correctos
        $verificacionTipoDoc = ParametroTema::query()->find($tipoDocumentoParametroTemaId);
        if (! $verificacionTipoDoc || $verificacionTipoDoc->tema_id !== 2 || $verificacionTipoDoc->parametro_id !== $tipoDocumentoParametroId) {
            throw new ParametroTemaCreationException(2, $tipoDocumentoParametroId, "El ID devuelto ({$tipoDocumentoParametroTemaId}) no es válido para tipo_documento. Tema_id: " . ($verificacionTipoDoc->tema_id ?? 'null') . ", Parametro_id: " . ($verificacionTipoDoc->parametro_id ?? 'null'));
        }

        return [
            'tipo_documento' => $tipoDocumentoParametroTemaId,
            'numero_documento' => $numeroDocumento . $timestamp,
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
            'primer_apellido' => $apellidos[array_rand($apellidos)],
            'segundo_apellido' => $segundoApellido,
            'fecha_nacimiento' => date('Y-m-d', strtotime('-' . rand(20, 55) . ' years -' . rand(0, 365) . ' days')),
            'genero' => $generoParametroTemaId,
            'telefono' => (rand(1, 100) <= 60) ? '60' . rand(10000000, 99999999) : null,
            'celular' => '3' . rand(100000000, 999999999),
            'email' => $email,
            'pais_id' => $ubicacion['pais_id'] ?? null,
            'departamento_id' => $ubicacion['departamento_id'] ?? null,
            'municipio_id' => $ubicacion['municipio_id'] ?? null,
            'direccion' => 'Calle ' . rand(1, 100) . ' #' . rand(1, 50) . '-' . rand(1, 99),
            'status' => 1,
            'user_create_id' => $userId,
            'user_edit_id' => $userId,
        ];
    }

    /**
     * Obtiene el ID de parametros_temas basado en tema_id y parametro_id, o lo crea si no existe
     */
    private function obtenerOCrearParametroTema(int $temaId, int $parametroId, int $userId): int
    {
        if (! Schema::hasTable('parametros_temas')) {
            throw new DatabaseTableNotFoundException('parametros_temas');
        }

        // Buscar el parametro_tema existente
        $parametroTema = ParametroTema::query()
            ->where('tema_id', $temaId)
            ->where('parametro_id', $parametroId)
            ->first();

        if ($parametroTema && $parametroTema->id) {
            // Verificar que el ID realmente existe en parametros_temas y tiene el tema_id y parametro_id correctos
            $verificacion = ParametroTema::query()->find($parametroTema->id);
            if (! $verificacion) {
                // Si no se encuentra, continuar con la creación
            } elseif ($verificacion->tema_id === $temaId && $verificacion->parametro_id === $parametroId) {
                // Validación final antes de devolver - asegurar que los valores coinciden exactamente
                if ($verificacion->tema_id !== $temaId || $verificacion->parametro_id !== $parametroId) {
                    throw new ParametroTemaCreationException($temaId, $parametroId, "El ID encontrado ({$parametroTema->id}) no coincide con los valores esperados. Tema_id: {$verificacion->tema_id}, Parametro_id: {$verificacion->parametro_id}");
                }
                return (int) $parametroTema->id;
            } else {
                // Si los valores no coinciden, continuar con la creación en lugar de devolver un ID incorrecto
            }
        }

        // Verificar que el usuario existe, si no usar null
        $validUserId = User::query()->find($userId) ? $userId : null;

        // Si no existe, buscar o crear el tema
        if (Schema::hasTable('temas') && ! Tema::query()->find($temaId)) {
            $temaNombres = [
                2 => 'TIPO DE DOCUMENTO',
                3 => 'GENERO',
            ];
            Tema::query()->firstOrCreate(
                ['id' => $temaId],
                [
                    'name' => $temaNombres[$temaId] ?? 'TEMA ' . $temaId,
                    'status' => 1,
                    'user_create_id' => $validUserId,
                    'user_edit_id' => $validUserId,
                ]
            );
        }

        // Buscar o crear el parámetro
        if (Schema::hasTable('parametros') && ! Parametro::query()->find($parametroId)) {
            $parametroNombres = [
                3 => 'CÉDULA DE CIUDADANÍA',
                4 => 'CÉDULA DE EXTRANJERÍA',
                5 => 'PASAPORTE',
                6 => 'TARJETA DE IDENTIDAD',
                9 => 'MASCULINO',
                10 => 'FEMENINO',
                11 => 'NO DEFINE',
            ];
            Parametro::query()->firstOrCreate(
                ['id' => $parametroId],
                [
                    'name' => $parametroNombres[$parametroId] ?? 'PARAMETRO ' . $parametroId,
                    'status' => 1,
                    'user_create_id' => $validUserId,
                    'user_edit_id' => $validUserId,
                ]
            );
        }

        // Crear el parametro_tema
        try {
            $parametroTema = ParametroTema::query()->create([
                'tema_id' => $temaId,
                'parametro_id' => $parametroId,
                'status' => 1,
                'user_create_id' => $validUserId,
                'user_edit_id' => $validUserId,
            ]);

            // Verificar que se creó correctamente
            if (! $parametroTema || ! $parametroTema->id) {
                throw new ParametroTemaCreationException($temaId, $parametroId);
            }

            // Verificar que el ID realmente existe en parametros_temas y tiene el tema_id y parametro_id correctos
            $verificacion = ParametroTema::query()->find($parametroTema->id);
            if (! $verificacion || $verificacion->tema_id !== $temaId || $verificacion->parametro_id !== $parametroId) {
                throw new ParametroTemaCreationException($temaId, $parametroId, 'El parametro_tema se creó pero no se puede verificar o tiene valores incorrectos');
            }

            // Validación final antes de devolver
            $verificacionFinal = ParametroTema::query()->find($parametroTema->id);
            if (! $verificacionFinal || $verificacionFinal->tema_id !== $temaId || $verificacionFinal->parametro_id !== $parametroId) {
                throw new ParametroTemaCreationException($temaId, $parametroId, "El ID creado ({$parametroTema->id}) no coincide con los valores esperados después de la creación");
            }

            return $parametroTema->id;
        } catch (\Exception $e) {
            // Si falla la creación, intentar buscar nuevamente (puede haber sido creado por otro proceso)
            $parametroTema = ParametroTema::query()
                ->where('tema_id', $temaId)
                ->where('parametro_id', $parametroId)
                ->first();

            if ($parametroTema && $parametroTema->id) {
                // Verificar que el ID realmente existe y tiene el tema_id y parametro_id correctos
                $verificacion = ParametroTema::query()->find($parametroTema->id);
                if ($verificacion && $verificacion->tema_id === $temaId && $verificacion->parametro_id === $parametroId) {
                    // Validación final antes de devolver
                    if ($verificacion->tema_id !== $temaId || $verificacion->parametro_id !== $parametroId) {
                        throw new ParametroTemaCreationException($temaId, $parametroId, "El ID encontrado después del error ({$parametroTema->id}) no coincide con los valores esperados");
                    }
                    return $parametroTema->id;
                }
            }

            throw new ParametroTemaCreationException($temaId, $parametroId, $e->getMessage());
        }
    }

    /**
     * Obtiene una ubicación válida (pais, departamento, municipio) o valores por defecto
     */
    private function obtenerUbicacionValida(): array
    {
        // Intentar obtener una ubicación existente de la base de datos
        // Solo usar valores que realmente existen para evitar problemas de foreign keys
        if (Schema::hasTable('municipios') && Schema::hasTable('departamentos') && Schema::hasTable('pais')) {
            try {
                // Obtener un municipio con su departamento y país validando que todo existe
                $ubicacion = DB::table('municipios')
                    ->join('departamentos', 'municipios.departamento_id', '=', 'departamentos.id')
                    ->join('pais', 'departamentos.pais_id', '=', 'pais.id')
                    ->whereNotNull('municipios.departamento_id')
                    ->whereNotNull('departamentos.pais_id')
                    ->select(
                        'municipios.id as municipio_id',
                        'municipios.departamento_id as departamento_id',
                        'departamentos.pais_id as pais_id'
                    )
                    ->inRandomOrder()
                    ->first();

                if ($ubicacion && 
                    isset($ubicacion->municipio_id) && 
                    isset($ubicacion->departamento_id) && 
                    isset($ubicacion->pais_id)) {
                    // Verificar que el municipio realmente pertenece al departamento
                    $verificacion = DB::table('municipios')
                        ->where('id', $ubicacion->municipio_id)
                        ->where('departamento_id', $ubicacion->departamento_id)
                        ->exists();
                    
                    if ($verificacion) {
                        return [
                            'pais_id' => (int) $ubicacion->pais_id,
                            'departamento_id' => (int) $ubicacion->departamento_id,
                            'municipio_id' => (int) $ubicacion->municipio_id,
                        ];
                    }
                }
            } catch (\Exception $e) {
                // Continuar si hay error
            }
        }

        // Si no hay datos válidos, usar null (los campos son nullable)
        // En tests, los seeders deberían proporcionar estos datos
        return [
            'pais_id' => null,
            'departamento_id' => null,
            'municipio_id' => null,
        ];
    }
}
