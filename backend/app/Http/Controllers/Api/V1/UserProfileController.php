<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Services\UserProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller: UserProfileController
 *
 * Controlador para endpoints de perfil de usuario.
 *
 * Endpoints:
 * - GET /api/v1/user/profile - Obtener perfil del usuario autenticado
 * - PUT /api/v1/user/profile - Actualizar perfil (nombre, email)
 *
 * @see TR-006(MH)-visualización-de-perfil-de-usuario.md
 * @see TR-007(SH)-edición-de-perfil-de-usuario.md
 */
class UserProfileController extends Controller
{
    /**
     * Servicio de perfil de usuario
     */
    protected UserProfileService $profileService;

    /**
     * Constructor
     *
     * @param UserProfileService $profileService Servicio inyectado
     */
    public function __construct(UserProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Obtener perfil del usuario autenticado
     * 
     * Retorna los datos del perfil del usuario que realizó la petición.
     * Requiere autenticación (middleware auth:sanctum).
     * 
     * @param Request $request Request con usuario autenticado
     * @return JsonResponse Respuesta con datos del perfil
     * 
     * @response 200 {
     *   "error": 0,
     *   "respuesta": "Perfil obtenido correctamente",
     *   "resultado": {
     *     "user_code": "JPEREZ",
     *     "nombre": "Juan Pérez",
     *     "email": "juan.perez@ejemplo.com",
     *     "tipo_usuario": "usuario",
     *     "es_supervisor": false,
     *     "created_at": "2026-01-27T10:30:00+00:00"
     *   }
     * }
     * 
     * @response 401 {
     *   "error": 4001,
     *   "respuesta": "No autenticado",
     *   "resultado": {}
     * }
     */
    public function show(Request $request): JsonResponse
    {
        try {
            // Obtener usuario autenticado
            $user = $request->user();
            
            // Obtener datos del perfil
            $profileData = $this->profileService->getProfile($user);

            return response()->json([
                'error' => 0,
                'respuesta' => 'Perfil obtenido correctamente',
                'resultado' => $profileData
            ], 200);

        } catch (\Exception $e) {
            // Error inesperado
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) []
            ], 500);
        }
    }

    /**
     * Actualizar perfil del usuario autenticado (nombre, email).
     * Requiere autenticación (auth:sanctum). Código no modificable.
     *
     * @param UpdateProfileRequest $request Request validado con nombre y email
     * @return JsonResponse 200 con perfil actualizado, 422 validación
     *
     * @see TR-007(SH)-edición-de-perfil-de-usuario.md
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();
            $profileData = $this->profileService->updateProfile($user, $data);

            return response()->json([
                'error' => 0,
                'respuesta' => 'Perfil actualizado correctamente',
                'resultado' => $profileData,
            ], 200);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) [],
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 9999,
                'respuesta' => 'Error inesperado del servidor',
                'resultado' => (object) [],
            ], 500);
        }
    }
}
