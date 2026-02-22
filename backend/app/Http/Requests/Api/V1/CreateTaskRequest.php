<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * FormRequest: CreateTaskRequest
 * 
 * Valida los datos de entrada para crear un nuevo registro de tarea.
 * 
 * Campos validados:
 * - fecha: fecha de la tarea (requerido, formato YMD YYYY-MM-DD)
 * - cliente_id: ID del cliente (requerido, existe, activo, no inhabilitado)
 * - tipo_tarea_id: ID del tipo de tarea (requerido, existe, activo, genérico o asignado al cliente)
 * - duracion_minutos: duración en minutos (requerido, > 0, múltiplo de 15, <= 1440)
 * - sin_cargo: indica si es sin cargo (boolean, default: false)
 * - presencial: indica si es presencial (boolean, default: false)
 * - observacion: descripción de la tarea (requerido, no vacío)
 * - usuario_id: ID del empleado (opcional, solo para supervisores, existe, activo)
 * 
 * @see TR-028(MH)-carga-de-tarea-diaria.md
 */
class CreateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Requiere autenticación (middleware auth:sanctum).
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
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
                        // Verificar que el tipo de tarea sea genérico o esté asignado al cliente
                        $tipoTarea = \App\Models\TipoTarea::find($value);
                        if (!$tipoTarea) {
                            $fail('El tipo de tarea no existe.');
                            return;
                        }

                        // Si es genérico, está disponible para todos
                        if ($tipoTarea->is_generico) {
                            return;
                        }

                        // Si no es genérico, debe estar asignado al cliente
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
                    // Validar que sea múltiplo de 15
                    if ($value % 15 !== 0) {
                        $fail('La duración debe ser múltiplo de 15 minutos.');
                    }
                },
            ],
            'sin_cargo' => [
                'nullable',
                'boolean',
            ],
            'presencial' => [
                'nullable',
                'boolean',
            ],
            'observacion' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (trim($value) === '') {
                        $fail('La observación es obligatoria y no puede estar vacía.');
                    }
                },
            ],
            'usuario_id' => [
                'nullable',
                'integer',
                'exists:PQ_PARTES_USUARIOS,id',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que el cliente esté activo y no inhabilitado
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

            // Validar que el tipo de tarea esté activo y no inhabilitado
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

            // Validar que el empleado esté activo y no inhabilitado (si se proporciona)
            if ($this->has('usuario_id') && $this->input('usuario_id') !== null) {
                $usuario = \App\Models\Usuario::find($this->input('usuario_id'));
                if ($usuario) {
                    if (!$usuario->activo) {
                        $validator->errors()->add('usuario_id', 'El empleado seleccionado está inactivo.');
                    }
                    if ($usuario->inhabilitado) {
                        $validator->errors()->add('usuario_id', 'El empleado seleccionado está inhabilitado.');
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'La fecha debe ser una fecha válida',
            'fecha.date_format' => 'La fecha debe tener formato YYYY-MM-DD',
            'cliente_id.required' => 'El cliente es obligatorio',
            'cliente_id.integer' => 'El cliente debe ser un número entero',
            'cliente_id.exists' => 'El cliente seleccionado no existe',
            'tipo_tarea_id.required' => 'El tipo de tarea es obligatorio',
            'tipo_tarea_id.integer' => 'El tipo de tarea debe ser un número entero',
            'tipo_tarea_id.exists' => 'El tipo de tarea seleccionado no existe',
            'duracion_minutos.required' => 'La duración es obligatoria',
            'duracion_minutos.integer' => 'La duración debe ser un número entero',
            'duracion_minutos.min' => 'La duración debe ser mayor a cero',
            'duracion_minutos.max' => 'La duración no puede exceder 1440 minutos (24 horas)',
            'sin_cargo.boolean' => 'El campo "sin cargo" debe ser verdadero o falso',
            'presencial.boolean' => 'El campo "presencial" debe ser verdadero o falso',
            'observacion.required' => 'La observación es obligatoria',
            'observacion.string' => 'La observación debe ser texto',
            'usuario_id.integer' => 'El empleado debe ser un número entero',
            'usuario_id.exists' => 'El empleado seleccionado no existe',
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
            'fecha' => 'fecha',
            'cliente_id' => 'cliente',
            'tipo_tarea_id' => 'tipo de tarea',
            'duracion_minutos' => 'duración',
            'sin_cargo' => 'sin cargo',
            'presencial' => 'presencial',
            'observacion' => 'observación',
            'usuario_id' => 'empleado',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * 
     * Retorna respuesta en formato envelope estándar con código de error 4220.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
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

    /**
     * Prepare the data for validation.
     * 
     * Establece valores por defecto para campos booleanos si no están presentes.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Establecer valores por defecto para campos booleanos
        if (!$this->has('sin_cargo')) {
            $this->merge(['sin_cargo' => false]);
        }
        if (!$this->has('presencial')) {
            $this->merge(['presencial' => false]);
        }
    }
}
