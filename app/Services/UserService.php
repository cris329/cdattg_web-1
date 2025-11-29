<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Crear o actualizar usuario para aspirante
     */
    public function createOrUpdateForAspirante(array $data, Persona $persona): User
    {
        $existingUser = User::where('email', $data['email'])->first();

        if (!$existingUser) {
            return $this->createUserForAspirante($data, $persona);
        }

        return $this->updateUserRoleForAspirante($existingUser);
    }

    /**
     * Crear nuevo usuario para aspirante
     */
    private function createUserForAspirante(array $data, Persona $persona): User
    {
        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['numero_documento']),
            'status' => 1,
            'persona_id' => $persona->id,
        ]);

        $user->assignRole('ASPIRANTE');

        // Enviar email de verificación automáticamente
        $user->sendEmailVerificationNotification();

        return $user;
    }

    /**
     * Actualizar rol de usuario existente
     */
    private function updateUserRoleForAspirante(User $user): User
    {
        // Si el usuario tiene rol de visitante, cambiarlo a aspirante
        if ($user->hasRole('VISITANTE')) {
            $user->removeRole('VISITANTE');
            $user->assignRole('ASPIRANTE');
        }

        return $user;
    }

    /**
     * Crear usuario básico
     */
    public function create(array $data): User
    {
        return User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? $data['numero_documento']),
            'status' => $data['status'] ?? 1,
            'persona_id' => $data['persona_id'] ?? null,
        ]);
    }

    /**
     * Actualizar usuario
     */
    public function update(User $user, array $data): bool
    {
        $updateData = [];

        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }

        if (isset($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        if (isset($data['persona_id'])) {
            $updateData['persona_id'] = $data['persona_id'];
        }

        return $user->update($updateData);
    }

    /**
     * Asignar rol a usuario
     */
    public function assignRole(User $user, string $role): void
    {
        $user->assignRole($role);
    }

    /**
     * Remover rol de usuario
     */
    public function removeRole(User $user, string $role): void
    {
        $user->removeRole($role);
    }

    /**
     * Verificar si usuario tiene rol
     */
    public function hasRole(User $user, string $role): bool
    {
        return $user->hasRole($role);
    }

    /**
     * Activar usuario
     */
    public function activate(User $user): bool
    {
        return $user->update(['status' => 1]);
    }

    /**
     * Desactivar usuario
     */
    public function deactivate(User $user): bool
    {
        return $user->update(['status' => 0]);
    }

    /**
     * Enviar notificación de verificación de email
     */
    public function sendEmailVerification(User $user): void
    {
        $user->sendEmailVerificationNotification();
    }

    /**
     * Verificar email del usuario
     */
    public function markEmailAsVerified(User $user): bool
    {
        return $user->markEmailAsVerified();
    }

    /**
     * Buscar usuario por email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Buscar usuario por persona
     */
    public function findByPersona(int $personaId): ?User
    {
        return User::where('persona_id', $personaId)->first();
    }
}
