<?php

declare(strict_types=1);

namespace Database\Factories\Concerns;

use App\Exceptions\DatabaseTableNotFoundException;
use App\Exceptions\UserFactoryException;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait HasUserId
{
    /**
     * Obtiene un ID de usuario existente o crea uno nuevo
     */
    protected function getUserId(): int
    {
        if (!Schema::hasTable('users')) {
            throw new DatabaseTableNotFoundException('users');
        }

        $existingUserId = $this->obtenerUsuarioExistente();
        if ($existingUserId !== null) {
            return $existingUserId;
        }

        try {
            return User::factory()->create()->id;
        } catch (\Exception $e) {
            return $this->crearUsuarioAlternativo($e);
        }
    }

    /**
     * Obtiene un usuario existente aleatorio
     */
    private function obtenerUsuarioExistente(): ?int
    {
        try {
            return User::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Crea un usuario alternativo cuando falla la factory estándar
     */
    private function crearUsuarioAlternativo(\Exception $e): int
    {
        $userId = $this->crearUsuarioBasico();
        if ($userId !== null) {
            return $userId;
        }

        $defaultUserId = $this->obtenerUsuarioPorDefecto();
        if ($defaultUserId !== null) {
            return $defaultUserId;
        }

        throw new UserFactoryException(
            'No se pudo obtener o crear un User. ' .
            'Asegúrate de ejecutar los seeders necesarios (TemaSeeder, PersonaSeeder, UsersSeeder). ' .
            'Error original: ' . $e->getMessage(),
            0,
            $e
        );
    }

    /**
     * Intenta crear un usuario básico directamente en la base de datos
     */
    private function crearUsuarioBasico(): ?int
    {
        try {
            $personaId = $this->obtenerOCrearPersona();
            if ($personaId === null) {
                return null;
            }

            return $this->insertarUsuarioBasico($personaId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene o crea una persona básica
     */
    private function obtenerOCrearPersona(): ?int
    {
        if (!Schema::hasTable('personas')) {
            return null;
        }

        $personaId = $this->obtenerPersonaExistente();
        if ($personaId !== null) {
            return $personaId;
        }

        return $this->crearPersonaBasica();
    }

    /**
     * Obtiene una persona existente aleatoria
     */
    private function obtenerPersonaExistente(): ?int
    {
        try {
            return Persona::query()->inRandomOrder()->value('id');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Crea una persona básica directamente en la base de datos
     */
    private function crearPersonaBasica(): ?int
    {
        $uniqueId = 'persona_' . bin2hex(random_bytes(8));
        $timestamp = time() . random_int(1000, 9999);

        return DB::table('personas')->insertGetId([
            'numero_documento' => $uniqueId . $timestamp,
            'primer_nombre' => 'Usuario',
            'primer_apellido' => 'Factory',
            'email' => strtolower($uniqueId . $timestamp . '@factory.test'),
            'status' => 1,
            'user_create_id' => null,
            'user_edit_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Inserta un usuario básico directamente en la base de datos
     */
    private function insertarUsuarioBasico(int $personaId): int
    {
        $uniqueId = 'user_' . bin2hex(random_bytes(8));
        $timestamp = time() . random_int(1000, 9999);

        return DB::table('users')->insertGetId([
            'email' => strtolower($uniqueId . $timestamp . '@factory.test'),
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'status' => 1,
            'persona_id' => $personaId,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Obtiene el usuario por defecto si existe
     */
    private function obtenerUsuarioPorDefecto(): ?int
    {
        $defaultUserId = (int) config('app.audit_default_user_id', 1);

        return User::query()->where('id', $defaultUserId)->exists() ? $defaultUserId : null;
    }
}

