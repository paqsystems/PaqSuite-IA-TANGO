<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * FormRequest: ForgotPasswordRequest
 *
 * Valida code_or_email para POST /api/v1/auth/forgot-password.
 *
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */
class ForgotPasswordRequest extends FormRequest
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
            'code_or_email' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'code_or_email.required' => 'El código de usuario o email es requerido.',
            'code_or_email.min' => 'El código de usuario o email no puede estar vacío.',
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
