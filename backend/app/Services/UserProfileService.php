<?php

namespace App\Services;

use App\Models\User;

/**
 * Service: UserProfileService
 *
 * Servicio de perfil de usuario simplificado (sin sistema de partes).
 * Usa únicamente la tabla USERS.
 */
class UserProfileService
{
    public function getProfile(User $user): array
    {
        return [
            'user_code' => $user->code,
            'nombre' => $user->name ?? $user->code,
            'email' => $user->email,
            'tipo_usuario' => 'usuario',
            'es_supervisor' => false,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    public function updateProfile(User $user, array $data): array
    {
        $user->name = $data['nombre'] ?? $user->name;
        $user->email = isset($data['email']) && $data['email'] !== '' ? $data['email'] : null;
        $user->save();

        return $this->getProfile($user->fresh());
    }
}
