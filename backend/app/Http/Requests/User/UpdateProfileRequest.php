<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * FormRequest: UpdateProfileRequest
 *
 * Valida nombre y email para PUT /api/v1/user/profile.
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
        return [
            'nombre' => ['required', 'string', 'min:1', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('USERS', 'email')->ignore($user->id)],
        ];
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
