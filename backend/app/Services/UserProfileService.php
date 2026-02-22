<?php

namespace App\Services;

use App\Models\User;
use App\Models\Usuario;
use App\Models\Cliente;

/**
 * Service: UserProfileService
 * 
 * Servicio para obtener información del perfil de usuario.
 * Consulta los datos del usuario autenticado según su tipo (empleado o cliente).
 * 
 * Flujo:
 * 1. Recibe el User autenticado
 * 2. Determina si es empleado (PQ_PARTES_USUARIOS) o cliente (PQ_PARTES_CLIENTES)
 * 3. Retorna datos del perfil según el tipo
 * 
 * @see TR-006(MH)-visualización-de-perfil-de-usuario.md
 */
class UserProfileService
{
    /**
     * Obtener datos del perfil del usuario autenticado
     *
     * @param User $user Usuario autenticado
     * @return array Datos del perfil formateados
     */
    public function getProfile(User $user): array
    {
        // Buscar si es empleado (PQ_PARTES_USUARIOS)
        $empleado = Usuario::where('user_id', $user->id)->first();
        
        if ($empleado) {
            return $this->buildEmpleadoProfile($user, $empleado);
        }
        
        // Buscar si es cliente (PQ_PARTES_CLIENTES)
        $cliente = Cliente::where('user_id', $user->id)->first();
        
        if ($cliente) {
            return $this->buildClienteProfile($user, $cliente);
        }
        
        // Usuario sin perfil en ninguna tabla (caso edge)
        // Retornar datos mínimos del User
        return $this->buildMinimalProfile($user);
    }

    /**
     * Actualizar perfil del usuario autenticado (nombre, email).
     * Empleado: actualiza PQ_PARTES_USUARIOS. Cliente: actualiza PQ_PARTES_CLIENTES.
     * El código no es modificable.
     *
     * @param User $user Usuario autenticado
     * @param array $data ['nombre' => string, 'email' => string|null]
     * @return array Datos del perfil actualizados (mismo formato que getProfile)
     * @throws \RuntimeException Si el usuario no tiene perfil en PQ_PARTES_USUARIOS ni PQ_PARTES_CLIENTES
     */
    public function updateProfile(User $user, array $data): array
    {
        $empleado = Usuario::where('user_id', $user->id)->first();
        if ($empleado) {
            $empleado->nombre = $data['nombre'];
            $empleado->email = isset($data['email']) && $data['email'] !== '' ? $data['email'] : null;
            $empleado->save();
            return $this->buildEmpleadoProfile($user, $empleado->fresh());
        }

        $cliente = Cliente::where('user_id', $user->id)->first();
        if ($cliente) {
            $cliente->nombre = $data['nombre'];
            $cliente->email = isset($data['email']) && $data['email'] !== '' ? $data['email'] : null;
            $cliente->save();
            return $this->buildClienteProfile($user, $cliente->fresh());
        }

        throw new \RuntimeException('Usuario sin perfil en PQ_PARTES_USUARIOS ni PQ_PARTES_CLIENTES');
    }

    /**
     * Construir perfil de empleado
     *
     * @param User $user Datos de autenticación
     * @param Usuario $empleado Datos del empleado
     * @return array
     */
    private function buildEmpleadoProfile(User $user, Usuario $empleado): array
    {
        return [
            'user_code' => $user->code,
            'nombre' => $empleado->nombre,
            'email' => $empleado->email, // Puede ser null
            'tipo_usuario' => 'usuario',
            'es_supervisor' => (bool) $empleado->supervisor,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * Construir perfil de cliente
     *
     * @param User $user Datos de autenticación
     * @param Cliente $cliente Datos del cliente
     * @return array
     */
    private function buildClienteProfile(User $user, Cliente $cliente): array
    {
        return [
            'user_code' => $user->code,
            'nombre' => $cliente->nombre, // Usamos nombre del cliente (razon_social)
            'email' => $cliente->email, // Puede ser null
            'tipo_usuario' => 'cliente',
            'es_supervisor' => false, // Clientes nunca son supervisores
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * Construir perfil mínimo (usuario sin registro en PQ_PARTES_*)
     *
     * @param User $user Datos de autenticación
     * @return array
     */
    private function buildMinimalProfile(User $user): array
    {
        return [
            'user_code' => $user->code,
            'nombre' => $user->code, // Usar code como fallback
            'email' => null,
            'tipo_usuario' => 'desconocido',
            'es_supervisor' => false,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
