<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * FormRequest: LoginRequest
 * 
 * Valida los datos de entrada para el endpoint de login.
 * 
 * Campos validados:
 * - usuario: código de usuario (requerido, no vacío)
 * - password: contraseña (requerido, no vacío, mínimo 8 caracteres)
 * 
 * Códigos de error:
 * - 1101: Código de usuario requerido
 * - 1102: Código de usuario no puede estar vacío
 * - 1103: Contraseña requerida
 * - 1104: Contraseña muy corta (mínimo 8 caracteres)
 * 
 * @see TR-001(MH)-login-de-empleado.md
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Endpoint público
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'usuario' => ['required', 'string', 'min:1'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'usuario.required' => 'El código de usuario es requerido',
            'usuario.string' => 'El código de usuario debe ser texto',
            'usuario.min' => 'El código de usuario no puede estar vacío',
            'password.required' => 'La contraseña es requerida',
            'password.string' => 'La contraseña debe ser texto',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'usuario' => 'código de usuario',
            'password' => 'contraseña',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * 
     * Retorna respuesta en formato envelope estándar con códigos de error específicos.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors();
        $failed = $validator->failed();
        
        // Determinar código de error según el campo y la regla que falló
        $errorCode = 1101; // Default: usuario requerido
        $errorMessage = 'Error de validación';
        
        if ($errors->has('usuario')) {
            // Verificar qué regla falló para usuario
            if (isset($failed['usuario']['Required'])) {
                $errorCode = 1101;
                $errorMessage = 'El código de usuario es requerido';
            } else {
                // Min u otra regla = campo vacío
                $errorCode = 1102;
                $errorMessage = 'El código de usuario no puede estar vacío';
            }
        } elseif ($errors->has('password')) {
            // Verificar qué regla falló para password
            if (isset($failed['password']['Required'])) {
                $errorCode = 1103;
                $errorMessage = 'La contraseña es requerida';
            } else {
                // Min u otra regla = muy corta
                $errorCode = 1104;
                $errorMessage = 'La contraseña debe tener al menos 8 caracteres';
            }
        }

        throw new HttpResponseException(
            response()->json([
                'error' => $errorCode,
                'respuesta' => $errorMessage,
                'resultado' => [
                    'errors' => $errors->toArray()
                ]
            ], 422)
        );
    }
}
