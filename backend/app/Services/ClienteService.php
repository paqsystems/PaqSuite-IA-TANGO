<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\ClienteTipoTarea;
use App\Models\User;
use App\Models\TipoCliente;
use App\Models\TipoTarea;
use App\Models\RegistroTarea;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Servicio: ClienteService
 *
 * Lógica de negocio para clientes. TR-008 (listado), TR-009 (creación), TR-010 (edición), TR-011 (eliminación), TR-012 (tipos de tarea).
 *
 * @see TR-009(MH)-creación-de-cliente.md
 * @see TR-010(MH)-edición-de-cliente.md
 * @see TR-011(MH)-eliminación-de-cliente.md
 * @see TR-012(MH)-asignación-de-tipos-de-tarea-a-cliente.md
 */
class ClienteService
{
    /** Error 403: solo supervisores */
    public const ERROR_FORBIDDEN = 3101;

    /** Error 404: cliente no encontrado */
    public const ERROR_NOT_FOUND = 4003;

    /** Error 2116: cliente debe tener al menos un tipo de tarea disponible */
    public const ERROR_SIN_TIPOS_TAREA = 2116;

    /** Error 2118: no se puede asignar tipo de tarea genérico */
    public const ERROR_TIPO_GENERICO = 2118;

    /** Error 2112: no se puede eliminar cliente con tareas asociadas */
    public const ERROR_TIENE_TAREAS = 2112;

    /** Error 4007: tipo de tarea no encontrado (TR-012) */
    public const ERROR_TIPO_TAREA_NOT_FOUND = 4007;

    /** Error 4205: tipo de tarea inactivo o inhabilitado (TR-012) */
    public const ERROR_TIPO_TAREA_INACTIVO = 4205;

    /** Error conflicto código duplicado (409) */
    public const ERROR_CODE_DUPLICATE = 4101;

    /** Error conflicto email duplicado (409) */
    public const ERROR_EMAIL_DUPLICATE = 4102;

    /**
     * Crear un nuevo cliente. Solo supervisores (validado en controller).
     * Si habilitar_acceso: crear User y luego Cliente con user_id.
     * Regla 2116: debe existir al menos un tipo genérico O el cliente tendrá tipos en HU-012 (validamos que exista al menos un genérico tras crear).
     *
     * @param array $data code, nombre, tipo_cliente_id, email?, password?, habilitar_acceso?, activo?, inhabilitado?
     * @return array Cliente creado (array para respuesta API)
     * @throws \Illuminate\Validation\ValidationException con código en el mensaje o \Exception
     */
    public function create(array $data): array
    {
        $code = trim($data['code'] ?? '');
        $nombre = trim($data['nombre'] ?? '');
        $tipoClienteId = isset($data['tipo_cliente_id']) ? (int) $data['tipo_cliente_id'] : 0;
        $email = isset($data['email']) ? trim($data['email']) : null;
        $password = $data['password'] ?? null;
        $habilitarAcceso = !empty($data['habilitar_acceso']);
        $activo = isset($data['activo']) ? (bool) $data['activo'] : true;
        $inhabilitado = isset($data['inhabilitado']) ? (bool) $data['inhabilitado'] : false;

        if ($code === '') {
            throw ValidationException::withMessages(['code' => ['El código es obligatorio.']]);
        }
        if ($nombre === '') {
            throw ValidationException::withMessages(['nombre' => ['El nombre es obligatorio.']]);
        }
        if ($tipoClienteId <= 0) {
            throw ValidationException::withMessages(['tipo_cliente_id' => ['El tipo de cliente es obligatorio.']]);
        }

        $tipoCliente = TipoCliente::where('id', $tipoClienteId)
            ->where('activo', true)
            ->where('inhabilitado', false)
            ->first();
        if (!$tipoCliente) {
            throw ValidationException::withMessages(['tipo_cliente_id' => ['Tipo de cliente no encontrado o no activo.']]);
        }

        if ($email !== null && $email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['email' => ['El email no tiene un formato válido.']]);
            }
            if (Cliente::where('email', $email)->exists()) {
                throw new \Exception('El email ya está registrado.', self::ERROR_EMAIL_DUPLICATE);
            }
        }

        if (Cliente::where('code', $code)->exists()) {
            throw new \Exception('El código del cliente ya existe.', self::ERROR_CODE_DUPLICATE);
        }

        if ($habilitarAcceso) {
            if ($password === null || trim($password) === '') {
                throw ValidationException::withMessages(['password' => ['La contraseña es obligatoria cuando se habilita acceso al sistema.']]);
            }
            if (strlen($password) < 8) {
                throw ValidationException::withMessages(['password' => ['La contraseña debe tener al menos 8 caracteres.']]);
            }
        if (User::where('code', $code)->exists()) {
            throw new \Exception('El código ya existe en el sistema de usuarios.', self::ERROR_CODE_DUPLICATE);
        }
        }

        // Regla 2116: debe existir al menos un tipo genérico (validar antes de crear)
        $tiposGenericos = TipoTarea::where('is_generico', true)
            ->where('activo', true)
            ->where('inhabilitado', false)
            ->count();
        if ($tiposGenericos === 0) {
            throw new \Exception('El cliente debe tener al menos un tipo de tarea disponible (genérico o asignado). Asigne tipos en la edición del cliente.', self::ERROR_SIN_TIPOS_TAREA);
        }

        return DB::transaction(function () use ($code, $nombre, $tipoClienteId, $email, $password, $habilitarAcceso, $activo, $inhabilitado) {
            $userId = null;
            if ($habilitarAcceso && $password !== null) {
                $user = new User();
                $user->code = $code;
                $user->password_hash = Hash::make($password);
                $user->activo = $activo;
                $user->inhabilitado = $inhabilitado;
                $user->save();
                $userId = $user->id;
            }

            $cliente = new Cliente();
            $cliente->code = $code;
            $cliente->nombre = $nombre;
            $cliente->tipo_cliente_id = $tipoClienteId;
            $cliente->email = $email ?: null;
            $cliente->activo = $activo;
            $cliente->inhabilitado = $inhabilitado;
            $cliente->user_id = $userId;
            $cliente->save();

            $cliente->load('tipoCliente');
            return [
                'id' => $cliente->id,
                'code' => $cliente->code,
                'nombre' => $cliente->nombre,
                'tipo_cliente_id' => $cliente->tipo_cliente_id,
                'tipo_cliente' => $cliente->tipoCliente ? [
                    'id' => $cliente->tipoCliente->id,
                    'code' => $cliente->tipoCliente->code,
                    'descripcion' => $cliente->tipoCliente->descripcion,
                ] : null,
                'email' => $cliente->email,
                'activo' => $cliente->activo,
                'inhabilitado' => $cliente->inhabilitado,
                'created_at' => $cliente->created_at?->toIso8601String(),
                'updated_at' => $cliente->updated_at?->toIso8601String(),
            ];
        });
    }

    /**
     * Obtener un cliente por ID para edición. Solo supervisores (validado en controller).
     *
     * @return array Cliente formateado para API (incl. tiene_acceso si user_id)
     * @throws \Exception con código ERROR_NOT_FOUND si no existe
     */
    public function getById(int $id): array
    {
        $cliente = Cliente::with('tipoCliente')->find($id);
        if (!$cliente) {
            throw new \Exception('Cliente no encontrado.', self::ERROR_NOT_FOUND);
        }
        return [
            'id' => $cliente->id,
            'code' => $cliente->code,
            'nombre' => $cliente->nombre,
            'tipo_cliente_id' => $cliente->tipo_cliente_id,
            'tipo_cliente' => $cliente->tipoCliente ? [
                'id' => $cliente->tipoCliente->id,
                'code' => $cliente->tipoCliente->code,
                'descripcion' => $cliente->tipoCliente->descripcion,
            ] : null,
            'email' => $cliente->email,
            'activo' => $cliente->activo,
            'inhabilitado' => $cliente->inhabilitado,
            'tiene_acceso' => $cliente->user_id !== null,
            'created_at' => $cliente->created_at?->toIso8601String(),
            'updated_at' => $cliente->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Actualizar un cliente existente. Solo supervisores (validado en controller).
     * Código no modificable. Sincroniza USERS si tiene user_id (password, activo, inhabilitado).
     * Si deshabilitar_acceso: user_id = null. Si habilitar_acceso y no tenía user_id: crear User y vincular.
     * Regla 2116: debe existir al menos un tipo genérico tras actualizar.
     *
     * @param int $id ID del cliente
     * @param array $data nombre, tipo_cliente_id, email?, password?, activo?, inhabilitado?, habilitar_acceso?
     * @return array Cliente actualizado (array para respuesta API)
     * @throws \Exception ERROR_NOT_FOUND, ERROR_EMAIL_DUPLICATE | ValidationException
     */
    public function update(int $id, array $data): array
    {
        $cliente = Cliente::with('tipoCliente')->find($id);
        if (!$cliente) {
            throw new \Exception('Cliente no encontrado.', self::ERROR_NOT_FOUND);
        }

        $nombre = trim($data['nombre'] ?? '');
        $tipoClienteId = isset($data['tipo_cliente_id']) ? (int) $data['tipo_cliente_id'] : 0;
        $email = isset($data['email']) ? trim($data['email']) : null;
        $password = $data['password'] ?? null;
        $habilitarAcceso = !empty($data['habilitar_acceso']);
        $activo = isset($data['activo']) ? (bool) $data['activo'] : $cliente->activo;
        $inhabilitado = isset($data['inhabilitado']) ? (bool) $data['inhabilitado'] : $cliente->inhabilitado;

        if ($nombre === '') {
            throw ValidationException::withMessages(['nombre' => ['El nombre es obligatorio.']]);
        }
        if ($tipoClienteId <= 0) {
            throw ValidationException::withMessages(['tipo_cliente_id' => ['El tipo de cliente es obligatorio.']]);
        }

        $tipoCliente = TipoCliente::where('id', $tipoClienteId)
            ->where('activo', true)
            ->where('inhabilitado', false)
            ->first();
        if (!$tipoCliente) {
            throw ValidationException::withMessages(['tipo_cliente_id' => ['Tipo de cliente no encontrado o no activo.']]);
        }

        if ($email !== null && $email !== '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['email' => ['El email no tiene un formato válido.']]);
            }
            if (Cliente::where('email', $email)->where('id', '!=', $id)->exists()) {
                throw new \Exception('El email ya está registrado.', self::ERROR_EMAIL_DUPLICATE);
            }
        }

        $teniaAcceso = $cliente->user_id !== null;
        if ($habilitarAcceso && !$teniaAcceso) {
            if ($password === null || trim($password) === '') {
                throw ValidationException::withMessages(['password' => ['La contraseña es obligatoria cuando se habilita acceso al sistema.']]);
            }
            if (strlen($password) < 8) {
                throw ValidationException::withMessages(['password' => ['La contraseña debe tener al menos 8 caracteres.']]);
            }
            if (User::where('code', $cliente->code)->exists()) {
                throw new \Exception('El código del cliente ya existe en el sistema de usuarios.', self::ERROR_CODE_DUPLICATE);
            }
        }

        // Regla 2116: debe existir al menos un tipo genérico (validar antes de cambiar datos que afecten)
        $tiposGenericos = TipoTarea::where('is_generico', true)
            ->where('activo', true)
            ->where('inhabilitado', false)
            ->count();
        if ($tiposGenericos === 0) {
            throw new \Exception('El cliente debe tener al menos un tipo de tarea disponible (genérico o asignado). Asigne tipos en la edición del cliente.', self::ERROR_SIN_TIPOS_TAREA);
        }

        return DB::transaction(function () use ($cliente, $nombre, $tipoClienteId, $email, $password, $habilitarAcceso, $activo, $inhabilitado, $teniaAcceso) {
            if ($habilitarAcceso && !$teniaAcceso && $password !== null) {
                $user = new User();
                $user->code = $cliente->code;
                $user->password_hash = Hash::make($password);
                $user->activo = $activo;
                $user->inhabilitado = $inhabilitado;
                $user->save();
                $cliente->user_id = $user->id;
            } elseif (!$habilitarAcceso && $teniaAcceso) {
                $cliente->user_id = null;
            } elseif ($teniaAcceso && $cliente->user_id) {
                $user = User::find($cliente->user_id);
                if ($user) {
                    if ($password !== null && trim($password) !== '') {
                        $user->password_hash = Hash::make($password);
                    }
                    $user->activo = $activo;
                    $user->inhabilitado = $inhabilitado;
                    $user->save();
                }
            }

            $cliente->nombre = $nombre;
            $cliente->tipo_cliente_id = $tipoClienteId;
            $cliente->email = $email ?: null;
            $cliente->activo = $activo;
            $cliente->inhabilitado = $inhabilitado;
            $cliente->save();
            $cliente->load('tipoCliente');

            return [
                'id' => $cliente->id,
                'code' => $cliente->code,
                'nombre' => $cliente->nombre,
                'tipo_cliente_id' => $cliente->tipo_cliente_id,
                'tipo_cliente' => $cliente->tipoCliente ? [
                    'id' => $cliente->tipoCliente->id,
                    'code' => $cliente->tipoCliente->code,
                    'descripcion' => $cliente->tipoCliente->descripcion,
                ] : null,
                'email' => $cliente->email,
                'activo' => $cliente->activo,
                'inhabilitado' => $cliente->inhabilitado,
                'updated_at' => $cliente->updated_at?->toIso8601String(),
            ];
        });
    }

    /**
     * Eliminar un cliente. Solo supervisores (validado en controller).
     * No se puede eliminar si tiene tareas asociadas (RegistroTarea). Error 2112.
     *
     * @param int $id ID del cliente
     * @throws \Exception ERROR_NOT_FOUND si no existe, ERROR_TIENE_TAREAS si tiene tareas
     */
    public function delete(int $id): void
    {
        $cliente = Cliente::find($id);
        if (!$cliente) {
            throw new \Exception('Cliente no encontrado.', self::ERROR_NOT_FOUND);
        }

        if (RegistroTarea::where('cliente_id', $id)->exists()) {
            throw new \Exception('No se puede eliminar un cliente que tiene tareas asociadas.', self::ERROR_TIENE_TAREAS);
        }

        $cliente->delete();
    }

    /**
     * Obtener tipos de tarea asignados a un cliente (solo no genéricos). TR-012(MH).
     * Solo supervisores (validado en controller).
     *
     * @param int $clienteId ID del cliente
     * @return array Lista de tipos asignados formateados para API
     * @throws \Exception ERROR_NOT_FOUND si el cliente no existe
     */
    public function getTiposTareaCliente(int $clienteId): array
    {
        $cliente = Cliente::find($clienteId);
        if (!$cliente) {
            throw new \Exception('Cliente no encontrado.', self::ERROR_NOT_FOUND);
        }

        $tipos = $cliente->tiposTarea()
            ->where('PQ_PARTES_TIPOS_TAREA.is_generico', false)
            ->where('PQ_PARTES_TIPOS_TAREA.activo', true)
            ->where('PQ_PARTES_TIPOS_TAREA.inhabilitado', false)
            ->orderBy('PQ_PARTES_TIPOS_TAREA.code')
            ->get(['PQ_PARTES_TIPOS_TAREA.id', 'PQ_PARTES_TIPOS_TAREA.code', 'PQ_PARTES_TIPOS_TAREA.descripcion', 'PQ_PARTES_TIPOS_TAREA.is_generico', 'PQ_PARTES_TIPOS_TAREA.activo', 'PQ_PARTES_TIPOS_TAREA.inhabilitado']);

        return $tipos->map(fn (TipoTarea $t) => [
            'id' => $t->id,
            'code' => $t->code,
            'descripcion' => $t->descripcion,
            'is_generico' => $t->is_generico,
            'activo' => $t->activo,
            'inhabilitado' => $t->inhabilitado,
        ])->values()->all();
    }

    /**
     * Actualizar asignación de tipos de tarea de un cliente. TR-012(MH).
     * Reemplaza las asignaciones por la lista dada. Solo tipos no genéricos, activos y no inhabilitados.
     * Regla 2116: si lista vacía y no hay tipos genéricos disponibles, lanza 2116.
     *
     * @param int $clienteId ID del cliente
     * @param array $tipoTareaIds Lista de IDs de tipos de tarea (puede ser vacía si hay genéricos)
     * @return array Lista de tipos asignados tras actualizar
     * @throws \Exception ERROR_NOT_FOUND, ERROR_SIN_TIPOS_TAREA, ERROR_TIPO_GENERICO, ERROR_TIPO_TAREA_NOT_FOUND, ERROR_TIPO_TAREA_INACTIVO
     */
    public function updateTiposTareaCliente(int $clienteId, array $tipoTareaIds): array
    {
        $cliente = Cliente::find($clienteId);
        if (!$cliente) {
            throw new \Exception('Cliente no encontrado.', self::ERROR_NOT_FOUND);
        }

        $tipoTareaIds = array_values(array_unique(array_map('intval', $tipoTareaIds)));
        $tipoTareaIds = array_filter($tipoTareaIds, fn ($id) => $id > 0);

        foreach ($tipoTareaIds as $tid) {
            $tipo = TipoTarea::find($tid);
            if (!$tipo) {
                throw new \Exception('Tipo de tarea no encontrado.', self::ERROR_TIPO_TAREA_NOT_FOUND);
            }
            if ($tipo->is_generico) {
                throw new \Exception('No se puede asignar un tipo de tarea genérico a un cliente.', self::ERROR_TIPO_GENERICO);
            }
            if (!$tipo->activo || $tipo->inhabilitado) {
                throw new \Exception('El tipo de tarea está inactivo o inhabilitado.', self::ERROR_TIPO_TAREA_INACTIVO);
            }
        }

        $hayGenericos = TipoTarea::where('is_generico', true)
            ->where('activo', true)
            ->where('inhabilitado', false)
            ->exists();

        if (count($tipoTareaIds) === 0 && !$hayGenericos) {
            throw new \Exception('El cliente debe tener al menos un tipo de tarea disponible (genérico o asignado).', self::ERROR_SIN_TIPOS_TAREA);
        }

        DB::transaction(function () use ($clienteId, $tipoTareaIds) {
            ClienteTipoTarea::where('cliente_id', $clienteId)->delete();
            foreach ($tipoTareaIds as $tid) {
                ClienteTipoTarea::create([
                    'cliente_id' => $clienteId,
                    'tipo_tarea_id' => $tid,
                ]);
            }
        });

        return $this->getTiposTareaCliente($clienteId);
    }
}
