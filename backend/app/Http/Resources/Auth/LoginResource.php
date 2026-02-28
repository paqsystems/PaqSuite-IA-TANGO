<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource: LoginResource
 * 
 * Formatea la respuesta de login exitoso con todos los campos requeridos.
 * 
 * Campos incluidos:
 * - token: Token de autenticación Sanctum
 * - user: Objeto con datos del usuario autenticado
 *   - user_id: ID del usuario en tabla USERS
 *   - user_code: Código del usuario
 *   - tipo_usuario: "usuario" (empleado) o "cliente"
 *   - usuario_id: ID del usuario en tabla USERS
 *   - cliente_id: null (schema simplificado sin tabla de clientes)
 *   - es_supervisor: true si el empleado es supervisor
 *   - nombre: Nombre completo del empleado/cliente
 *   - email: Email del empleado/cliente
 * 
 * @see TR-001(MH)-login-de-empleado.md
 */
class LoginResource extends JsonResource
{
    /**
     * Token de autenticación
     */
    private string $token;

    /**
     * Constructor
     *
     * @param mixed $resource Datos del usuario autenticado
     * @param string $token Token Sanctum generado
     */
    public function __construct($resource, string $token)
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'user' => [
                'user_id' => $this->resource['user_id'],
                'user_code' => $this->resource['user_code'],
                'tipo_usuario' => $this->resource['tipo_usuario'],
                'usuario_id' => $this->resource['usuario_id'],
                'cliente_id' => $this->resource['cliente_id'],
                'es_supervisor' => $this->resource['es_supervisor'],
                'nombre' => $this->resource['nombre'],
                'email' => $this->resource['email'],
            ]
        ];
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'error' => 0,
            'respuesta' => 'Autenticación exitosa',
        ];
    }

    /**
     * Customize the outgoing response for the resource.
     *
     * @param Request $request
     * @param \Illuminate\Http\JsonResponse $response
     */
    public function withResponse(Request $request, $response): void
    {
        $data = $response->getData(true);
        
        // Reestructurar para formato envelope estándar
        $envelope = [
            'error' => 0,
            'respuesta' => 'Autenticación exitosa',
            'resultado' => [
                'token' => $data['token'],
                'user' => $data['user'],
            ]
        ];
        
        $response->setData($envelope);
    }
}
