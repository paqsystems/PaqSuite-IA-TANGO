<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * FormRequest: ResetPasswordRequest
 *
 * Valida token, password y password_confirmation para POST /api/v1/auth/reset-password.
 *
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */
class ResetPasswordRequest extends FormRequest
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
            'token' => ['required', 'string', 'min:1'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'El token de recuperación es requerido.',
            'password.required' => 'La nueva contraseña es requerida.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 1000,
                'respuesta' => 'Los datos enviados no son válidos.',
                'resultado' => ['errors' => $validator->errors()->toArray()],
            ], 422)
        );
    }
}
