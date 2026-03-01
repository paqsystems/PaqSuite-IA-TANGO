<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controller: UserPreferencesController
 *
 * Preferencias de usuario (locale, menu_abrir_nueva_pestana).
 *
 * @see docs/04-tareas/000-Generalidades/TR-003-apertura-menu-misma-o-nueva-pestana.md
 * @see docs/04-tareas/000-Generalidades/TR-004-seleccion-idioma.md
 */
class UserPreferencesController extends Controller
{
    /**
     * GET /api/v1/user/preferences
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        return response()->json([
            'error' => 0,
            'respuesta' => 'OK',
            'resultado' => [
                'locale' => $user->locale ?? 'es',
                'menuAbrirNuevaPestana' => (bool) ($user->menu_abrir_nueva_pestana ?? false),
            ],
        ]);
    }

    /**
     * PUT /api/v1/user/preferences
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'error' => 401,
                'respuesta' => 'No autenticado',
                'resultado' => (object) [],
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'locale' => ['nullable', 'string', 'in:es,en'],
            'menuAbrirNuevaPestana' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 422,
                'respuesta' => 'Errores de validación',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422);
        }

        $data = $validator->validated();
        if (array_key_exists('locale', $data)) {
            $user->locale = $data['locale'];
        }
        if (array_key_exists('menuAbrirNuevaPestana', $data)) {
            $user->menu_abrir_nueva_pestana = $data['menuAbrirNuevaPestana'];
        }
        $user->save();

        return response()->json([
            'error' => 0,
            'respuesta' => 'Preferencias actualizadas',
            'resultado' => [
                'locale' => $user->locale ?? 'es',
                'menuAbrirNuevaPestana' => (bool) $user->menu_abrir_nueva_pestana,
            ],
        ]);
    }
}
