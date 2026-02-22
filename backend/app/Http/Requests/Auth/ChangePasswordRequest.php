<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * FormRequest: ChangePasswordRequest
 *
 * Valida los datos para el endpoint de cambio de contraseña (usuario autenticado).
 *
 * Campos: current_password, password, password_confirmation
 *
 * @see TR-005(SH)-cambio-de-contraseña-usuario-autenticado.md
 */
class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'min:1'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'La contraseña actual es requerida',
            'password.required' => 'La nueva contraseña es requerida',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'La confirmación de contraseña no coincide',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 422,
                'respuesta' => 'Error de validación',
                'resultado' => [
                    'errors' => $validator->errors()->toArray(),
                ],
            ], 422)
        );
    }
}
