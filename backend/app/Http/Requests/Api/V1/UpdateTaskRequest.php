<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * FormRequest: UpdateTaskRequest
 *
 * Valida los datos de entrada para actualizar un registro de tarea existente.
 * Mismas reglas que CreateTaskRequest. usuario_id es opcional y solo permitido para supervisores (TR-031).
 *
 * @see TR-029(MH)-edición-de-tarea-propia.md
 * @see TR-031(MH)-edición-de-tarea-supervisor.md
 */
class UpdateTaskRequest extends FormRequest
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
        $clienteId = $this->input('cliente_id');

        return [
            'fecha' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
            'cliente_id' => [
                'required',
                'integer',
                'exists:PQ_PARTES_CLIENTES,id',
            ],
            'tipo_tarea_id' => [
                'required',
                'integer',
                'exists:PQ_PARTES_TIPOS_TAREA,id',
                function ($attribute, $value, $fail) use ($clienteId) {
                    if ($clienteId) {
                        $tipoTarea = \App\Models\TipoTarea::find($value);
                        if (!$tipoTarea) {
                            $fail('El tipo de tarea no existe.');
                            return;
                        }
                        if ($tipoTarea->is_generico) {
                            return;
                        }
                        $asignado = \App\Models\ClienteTipoTarea::where('cliente_id', $clienteId)
                            ->where('tipo_tarea_id', $value)
                            ->exists();
                        if (!$asignado) {
                            $fail('El tipo de tarea no está disponible para el cliente seleccionado.');
                        }
                    }
                },
            ],
            'duracion_minutos' => [
                'required',
                'integer',
                'min:1',
                'max:1440',
                function ($attribute, $value, $fail) {
                    if ($value % 15 !== 0) {
                        $fail('La duración debe ser múltiplo de 15 minutos.');
                    }
                },
            ],
            'sin_cargo' => ['nullable', 'boolean'],
            'presencial' => ['nullable', 'boolean'],
            'observacion' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (trim($value) === '') {
                        $fail('La observación es obligatoria y no puede estar vacía.');
                    }
                },
            ],
            // TR-031: opcional; solo supervisores pueden enviarlo (validado en TaskService)
            'usuario_id' => [
                'nullable',
                'integer',
                'exists:PQ_PARTES_USUARIOS,id',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('cliente_id')) {
                $cliente = \App\Models\Cliente::find($this->input('cliente_id'));
                if ($cliente) {
                    if (!$cliente->activo) {
                        $validator->errors()->add('cliente_id', 'El cliente seleccionado está inactivo.');
                    }
                    if ($cliente->inhabilitado) {
                        $validator->errors()->add('cliente_id', 'El cliente seleccionado está inhabilitado.');
                    }
                }
            }
            if ($this->has('tipo_tarea_id')) {
                $tipoTarea = \App\Models\TipoTarea::find($this->input('tipo_tarea_id'));
                if ($tipoTarea) {
                    if (!$tipoTarea->activo) {
                        $validator->errors()->add('tipo_tarea_id', 'El tipo de tarea seleccionado está inactivo.');
                    }
                    if ($tipoTarea->inhabilitado) {
                        $validator->errors()->add('tipo_tarea_id', 'El tipo de tarea seleccionado está inhabilitado.');
                    }
                }
            }
            // TR-031: validar que empleado (usuario_id) esté activo si se envía
            if ($this->filled('usuario_id')) {
                $usuario = \App\Models\Usuario::find($this->input('usuario_id'));
                if ($usuario) {
                    if (!$usuario->activo || $usuario->inhabilitado) {
                        $validator->errors()->add('usuario_id', 'El empleado seleccionado no existe o no está activo.');
                    }
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('sin_cargo')) {
            $this->merge(['sin_cargo' => false]);
        }
        if (!$this->has('presencial')) {
            $this->merge(['presencial' => false]);
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'error' => 4220,
                'respuesta' => 'Errores de validación',
                'resultado' => [
                    'errors' => $validator->errors()->toArray()
                ]
            ], 422)
        );
    }
}
