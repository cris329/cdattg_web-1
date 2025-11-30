<?php

declare(strict_types=1);

namespace Database\Factories\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Schema;

trait HasUserId
{
    /**
     * Obtiene un ID de usuario existente o crea uno nuevo
     */
    protected function getUserId(): int
    {
        if (!Schema::hasTable('users')) {
            throw new \RuntimeException('La tabla users no existe. Ejecuta las migraciones primero.');
        }

        try {
            $userId = User::query()->inRandomOrder()->value('id');
            if ($userId) {
                return $userId;
            }
        } catch (\Exception $e) {
            // Ignorar error de consulta
        }

        // Si no hay usuarios, intentar crear uno
        try {
            return User::factory()->create()->id;
        } catch (\Exception $e) {
            // Si falla la creación (probablemente falta TemaSeeder), intentar crear un User básico directamente
            try {
                // Intentar crear una Persona básica sin parametros_temas específicos
                $personaId = null;
                if (Schema::hasTable('personas')) {
                    try {
                        $personaId = \App\Models\Persona::query()->inRandomOrder()->value('id');
                    } catch (\Exception $ex) {
                        // Ignorar error
                    }
                }
                
                // Si no hay Persona, crear una básica directamente en la BD
                if (!$personaId && Schema::hasTable('personas')) {
                    $uniqueId = uniqid('persona_', true);
                    $timestamp = time() . rand(1000, 9999);
                    
                    $personaId = \Illuminate\Support\Facades\DB::table('personas')->insertGetId([
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
                
                // Si tenemos una Persona, crear un User básico
                if ($personaId) {
                    $uniqueId = uniqid('user_', true);
                    $timestamp = time() . rand(1000, 9999);
                    
                    $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
                        'email' => strtolower($uniqueId . $timestamp . '@factory.test'),
                        'email_verified_at' => now(),
                        'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                        'status' => 1,
                        'persona_id' => $personaId,
                        'remember_token' => \Illuminate\Support\Str::random(10),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    return $userId;
                }
            } catch (\Exception $ex2) {
                // Si falla la creación directa, continuar con el flujo original
            }
            
            // Si falla todo, verificar si existe el ID por defecto
            $defaultUserId = (int) config('app.audit_default_user_id', 1);
            $userExists = User::query()->where('id', $defaultUserId)->exists();
            
            if ($userExists) {
                return $defaultUserId;
            }
            
            // Si no existe el User por defecto y no se puede crear, lanzar excepción clara
            throw new \RuntimeException(
                'No se pudo obtener o crear un User. ' .
                'Asegúrate de ejecutar los seeders necesarios (TemaSeeder, PersonaSeeder, UsersSeeder). ' .
                'Error original: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}

