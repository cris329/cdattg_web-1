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
     * Obtiene el ID de parametros_temas basado en tema_id y parametro_id
     * 
     * IMPORTANTE: Este método debe devolver el ID de la tabla parametros_temas, NO el parametro_id
     * El seeder debe crear estos registros antes de que el factory los use
     */
    private function obtenerOCrearParametroTema(int $temaId, int $parametroId, int $userId): int
    {
        if (! Schema::hasTable('parametros_temas')) {
            throw new DatabaseTableNotFoundException('parametros_temas');
        }

        // Usar el mismo método que PersonaSeeder::getParametroTemaId
        // IMPORTANTE: Necesitamos el ID de la columna 'id' de parametros_temas, NO el parametro_id
        // NO usar select() para evitar problemas con el orden de los campos
        $parametroTema = DB::table('parametros_temas')
            ->where('tema_id', $temaId)
            ->where('parametro_id', $parametroId)
            ->first();

        $parametroTemaId = null;
        if ($parametroTema) {
            // Acceder directamente al campo 'id' del resultado
            // CRÍTICO: Verificar que estamos accediendo al campo correcto
            // El campo 'id' debe ser el ID de parametros_temas, NO el parametro_id
            if (isset($parametroTema->id)) {
                $parametroTemaId = (int) $parametroTema->id;
            } else {
                throw new ParametroTemaCreationException($temaId, $parametroId, "ERROR CRÍTICO: El resultado no tiene un campo 'id'. Resultado: " . json_encode($parametroTema));
            }
            
            // Verificación final: el ID debe existir y tener los valores correctos
            // Esta verificación es suficiente - si el ID existe con los valores correctos, es válido
            // (incluso si coincide con el parametro_id por casualidad)
            $verificacion = DB::table('parametros_temas')
                ->where('id', $parametroTemaId)
                ->where('tema_id', $temaId)
                ->where('parametro_id', $parametroId)
                ->exists();

            if ($verificacion) {
                return $parametroTemaId;
            }
        }

        // Si no existe, intentar crearlo usando el modelo Tema y sync (igual que el seeder)
        $tema = Tema::find($temaId);
        if (!$tema) {
            throw new ParametroTemaCreationException($temaId, $parametroId, "El tema con ID {$temaId} no existe. Asegúrate de ejecutar TemaSeeder primero.");
        }

        // Usar sync para crear el ParametroTema, igual que el seeder
        $tema->parametros()->syncWithoutDetaching([
            $parametroId => ['status' => 1]
        ]);

        // Después de syncWithoutDetaching, buscar el registro recién creado
        // Usar el mismo método que PersonaSeeder::getParametroTemaId
        $parametroTema = DB::table('parametros_temas')
            ->where('tema_id', $temaId)
            ->where('parametro_id', $parametroId)
            ->orderBy('id', 'desc') // Obtener el más reciente
            ->first();
        
        $parametroTemaId = $parametroTema && isset($parametroTema->id) ? (int) $parametroTema->id : null;

        if (!$parametroTemaId) {
            throw new ParametroTemaCreationException($temaId, $parametroId, "No se pudo encontrar o crear el parametro_tema. Tema_id: {$temaId}, Parametro_id: {$parametroId}");
        }

        $parametroTemaId = (int) $parametroTemaId;
        
        // Verificación final: el ID debe existir y tener los valores correctos
        // Esta verificación es suficiente - si el ID existe con los valores correctos, es válido
        // (incluso si coincide con el parametro_id por casualidad)
        $verificacion = DB::table('parametros_temas')
            ->where('id', $parametroTemaId)
            ->where('tema_id', $temaId)
            ->where('parametro_id', $parametroId)
            ->exists();

        if (!$verificacion) {
            throw new ParametroTemaCreationException($temaId, $parametroId, "El ID ({$parametroTemaId}) no es válido. Tema_id: {$temaId}, Parametro_id: {$parametroId}");
        }

        return $parametroTemaId;
    }

    /**
     * Obtiene una ubicación válida (pais, departamento, municipio) o valores por defecto
     */
    private function obtenerUbicacionValida(): array
    {
        // En tests, usar null por defecto para evitar problemas de foreign keys
        // Los campos son nullable según la migración
        // Si se necesita una ubicación específica, se puede pasar explícitamente al factory
        return [
            'pais_id' => null,
            'departamento_id' => null,
            'municipio_id' => null,
        ];
    }
}
