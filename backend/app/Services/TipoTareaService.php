<?php

namespace App\Services;

use App\Models\TipoTarea;
use Illuminate\Validation\ValidationException;

/**
 * Servicio: TipoTareaService
 *
 * ABM de tipos de tarea (listado, creación, edición, eliminación). TR-023, TR-024, TR-025, TR-026, TR-027.
 *
 * @see docs/hu-tareas/TR-023(MH)-listado-de-tipos-de-tarea.md
 * @see docs/hu-tareas/TR-024(MH)-creación-de-tipo-de-tarea.md
 * @see docs/hu-tareas/TR-025(MH)-edición-de-tipo-de-tarea.md
 * @see docs/hu-tareas/TR-026(MH)-eliminación-de-tipo-de-tarea.md
 * @see docs/hu-tareas/TR-027(SH)-visualización-de-detalle-de-tipo-de-tarea.md
 */
class TipoTareaService
{
    public const ERROR_FORBIDDEN = 3101;
    public const ERROR_NOT_FOUND = 4003;
    /** Código duplicado (code ya existe) */
    public const ERROR_CODE_DUPLICATE = 4102;
    /** No se puede eliminar: tiene tareas o clientes asociados */
    public const ERROR_EN_USO = 2114;
    /** Solo puede haber un tipo de tarea por defecto */
    public const ERROR_YA_HAY_POR_DEFECTO = 2117;

    /**
     * Listado paginado con búsqueda y filtros.
     *
     * @return array{items: array, total: int, page: int, page_size: int}
     */
    public function listado(
        int $page,
        int $pageSize,
        ?string $search,
        $isGenerico,
        $isDefault,
        $activo,
        $inhabilitado,
        string $sort,
        string $sortDir
    ): array {
        $sortWhitelist = ['code', 'descripcion', 'is_generico', 'is_default', 'created_at', 'updated_at'];
        if (!in_array($sort, $sortWhitelist, true)) {
            $sort = 'descripcion';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        $query = TipoTarea::query()
            ->when($search !== null && trim($search) !== '', function ($q) use ($search) {
                $term = '%' . trim($search) . '%';
                $q->where(function ($q2) use ($term) {
                    $q2->where('code', 'like', $term)->orWhere('descripcion', 'like', $term);
                });
            })
            ->when($isGenerico !== null && $isGenerico !== '', function ($q) use ($isGenerico) {
                $q->where('is_generico', filter_var($isGenerico, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($isDefault !== null && $isDefault !== '', function ($q) use ($isDefault) {
                $q->where('is_default', filter_var($isDefault, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($activo !== null && $activo !== '', function ($q) use ($activo) {
                $q->where('activo', filter_var($activo, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($inhabilitado !== null && $inhabilitado !== '', function ($q) use ($inhabilitado) {
                $q->where('inhabilitado', filter_var($inhabilitado, FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy($sort, $sortDir);

        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);
        $items = $paginator->getCollection()->map(function (TipoTarea $t) {
            return $this->toArrayItem($t);
        })->values()->all();

        return [
            'items' => $items,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'page_size' => $paginator->perPage(),
        ];
    }

    /**
     * Obtener un tipo por ID (para edición/detalle).
     *
     * @throws \Exception ERROR_NOT_FOUND
     */
    public function getById(int $id): array
    {
        $tipo = TipoTarea::find($id);
        if (!$tipo) {
            throw new \Exception('Tipo de tarea no encontrado.', self::ERROR_NOT_FOUND);
        }
        return $this->toArrayItem($tipo);
    }

    /**
     * Obtener tipo por ID con clientes asociados (para detalle TR-027). Si es genérico, clientes vacío.
     *
     * @throws \Exception ERROR_NOT_FOUND
     */
    public function getByIdConClientes(int $id): array
    {
        $tipo = TipoTarea::with('clientes:id,code,nombre')->find($id);
        if (!$tipo) {
            throw new \Exception('Tipo de tarea no encontrado.', self::ERROR_NOT_FOUND);
        }
        $item = $this->toArrayItem($tipo);
        $item['clientes'] = $tipo->is_generico
            ? []
            : $tipo->clientes->map(fn ($c) => [
                'id' => $c->id,
                'code' => $c->code,
                'nombre' => $c->nombre,
            ])->values()->all();
        return $item;
    }

    /**
     * Crear tipo de tarea. Código único. Validar único is_default (2117). Si is_default=true forzar is_generico=true.
     *
     * @throws ValidationException
     * @throws \Exception ERROR_CODE_DUPLICATE, ERROR_YA_HAY_POR_DEFECTO
     */
    public function create(array $data): array
    {
        $code = trim($data['code'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $isGenerico = isset($data['is_generico']) ? (bool) $data['is_generico'] : false;
        $isDefault = isset($data['is_default']) ? (bool) $data['is_default'] : false;
        $activo = isset($data['activo']) ? (bool) $data['activo'] : true;
        $inhabilitado = isset($data['inhabilitado']) ? (bool) $data['inhabilitado'] : false;

        if ($code === '') {
            throw ValidationException::withMessages(['code' => ['El código es obligatorio.']]);
        }
        if ($descripcion === '') {
            throw ValidationException::withMessages(['descripcion' => ['La descripción es obligatoria.']]);
        }
        if (TipoTarea::where('code', $code)->exists()) {
            throw new \Exception('El código del tipo de tarea ya existe.', self::ERROR_CODE_DUPLICATE);
        }
        if ($isDefault && TipoTarea::where('is_default', true)->exists()) {
            throw new \Exception('Solo puede haber un tipo de tarea por defecto.', self::ERROR_YA_HAY_POR_DEFECTO);
        }
        if ($isDefault) {
            $isGenerico = true;
        }

        $tipo = TipoTarea::create([
            'code' => $code,
            'descripcion' => $descripcion,
            'is_generico' => $isGenerico,
            'is_default' => $isDefault,
            'activo' => $activo,
            'inhabilitado' => $inhabilitado,
        ]);

        return $this->getById($tipo->id);
    }

    /**
     * Actualizar tipo de tarea. Código no modificable. Validar único is_default (2117).
     *
     * @throws \Exception ERROR_NOT_FOUND, ERROR_YA_HAY_POR_DEFECTO
     * @throws ValidationException
     */
    public function update(int $id, array $data): array
    {
        $tipo = TipoTarea::find($id);
        if (!$tipo) {
            throw new \Exception('Tipo de tarea no encontrado.', self::ERROR_NOT_FOUND);
        }

        $descripcion = trim($data['descripcion'] ?? '');
        $isGenerico = isset($data['is_generico']) ? (bool) $data['is_generico'] : $tipo->is_generico;
        $isDefault = isset($data['is_default']) ? (bool) $data['is_default'] : $tipo->is_default;
        $activo = isset($data['activo']) ? (bool) $data['activo'] : $tipo->activo;
        $inhabilitado = isset($data['inhabilitado']) ? (bool) $data['inhabilitado'] : $tipo->inhabilitado;

        if ($descripcion === '') {
            throw ValidationException::withMessages(['descripcion' => ['La descripción es obligatoria.']]);
        }
        if ($isDefault && !$isGenerico) {
            $isGenerico = true;
        }
        if ($isDefault) {
            $otro = TipoTarea::where('is_default', true)->where('id', '!=', $id)->first();
            if ($otro) {
                throw new \Exception('Solo puede haber un tipo de tarea por defecto.', self::ERROR_YA_HAY_POR_DEFECTO);
            }
        }

        $tipo->descripcion = $descripcion;
        $tipo->is_generico = $isGenerico;
        $tipo->is_default = $isDefault;
        $tipo->activo = $activo;
        $tipo->inhabilitado = $inhabilitado;
        $tipo->save();

        return $this->getById($tipo->id);
    }

    /**
     * Eliminar tipo de tarea. No permite si tiene tareas o clientes asociados (2114).
     *
     * @throws \Exception ERROR_NOT_FOUND, ERROR_EN_USO
     */
    public function delete(int $id): void
    {
        $tipo = TipoTarea::find($id);
        if (!$tipo) {
            throw new \Exception('Tipo de tarea no encontrado.', self::ERROR_NOT_FOUND);
        }
        if ($tipo->registrosTarea()->exists()) {
            throw new \Exception('No se puede eliminar el tipo de tarea porque está en uso (tareas o clientes asociados).', self::ERROR_EN_USO);
        }
        if ($tipo->clientes()->exists()) {
            throw new \Exception('No se puede eliminar el tipo de tarea porque está en uso (tareas o clientes asociados).', self::ERROR_EN_USO);
        }
        $tipo->delete();
    }

    private function toArrayItem(TipoTarea $t): array
    {
        return [
            'id' => $t->id,
            'code' => $t->code,
            'descripcion' => $t->descripcion,
            'is_generico' => $t->is_generico,
            'is_default' => $t->is_default,
            'activo' => $t->activo,
            'inhabilitado' => $t->inhabilitado,
            'created_at' => $t->created_at?->toIso8601String(),
            'updated_at' => $t->updated_at?->toIso8601String(),
        ];
    }
}
