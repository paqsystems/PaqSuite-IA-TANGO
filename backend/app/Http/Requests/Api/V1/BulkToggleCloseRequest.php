<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest: BulkToggleCloseRequest
 *
 * Valida el body para procesamiento masivo cerrar/reabrir tareas.
 * task_ids: array de IDs, al menos uno (TR-042, TR-043).
 *
 * @see TR-042(SH)-procesamiento-masivo-de-tareas-cerrarreabrir.md
 * @see TR-043(SH)-validación-de-selección-para-procesamiento.md
 */
class BulkToggleCloseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'task_ids.required' => 'Debe seleccionar al menos una tarea',
            'task_ids.min' => 'Debe seleccionar al menos una tarea',
        ];
    }

    /**
     * Respuesta 422 con código 1212 cuando la validación falla
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'error' => 1212,
                'respuesta' => 'Debe seleccionar al menos una tarea',
                'resultado' => (object) [],
            ], 422)
        );
    }
}
