<?php

namespace App\Http\Requests\User;

use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * FormRequest: UpdateProfileRequest
 *
 * Valida nombre y email para PUT /api/v1/user/profile.
 * Código de usuario no se acepta en el body (no modificable).
 *
 * @see TR-007(SH)-edición-de-perfil-de-usuario.md
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $user = $this->user();
        $emailUniqueRule = $this->buildEmailUniqueRule($user);

        return [
            'nombre' => ['required', 'string', 'min:1', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', $emailUniqueRule],
        ];
    }

    /**
     * Regla unique para email excluyendo al usuario actual (empleado o cliente).
     */
    private function buildEmailUniqueRule($user): \Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Validation\Rules\Unique
    {
        $empleado = Usuario::where('user_id', $user->id)->first();
        if ($empleado) {
            return Rule::unique('PQ_PARTES_USUARIOS', 'email')->ignore($empleado->id);
        }
        $cliente = Cliente::where('user_id', $user->id)->first();
        if ($cliente) {
            return Rule::unique('PQ_PARTES_CLIENTES', 'email')->ignore($cliente->id);
        }
        return Rule::unique('PQ_PARTES_USUARIOS', 'email');
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre no puede estar vacío.',
            'email.email' => 'El email no tiene un formato válido.',
            'email.unique' => 'El email ya está en uso por otro usuario.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        throw new HttpResponseException(
            response()->json([
                'error' => 1000,
                'respuesta' => 'Los datos enviados no son válidos.',
                'resultado' => ['errors' => $errors],
            ], 422)
        );
    }
}
