<?php

namespace App\Services;

use App\Models\TipoCliente;
use Illuminate\Validation\ValidationException;

/**
 * Servicio: TipoClienteService
 *
 * ABM de tipos de cliente (listado, creación, edición, eliminación). TR-014, TR-015, TR-016, TR-017.
 *
 * @see TR-014(MH)-listado-de-tipos-de-cliente.md
 * @see TR-015(MH)-creación-de-tipo-de-cliente.md
 * @see TR-016(MH)-edición-de-tipo-de-cliente.md
 * @see TR-017(MH)-eliminación-de-tipo-de-cliente.md
 */
class TipoClienteService
{
    public const ERROR_FORBIDDEN = 3101;
    public const ERROR_NOT_FOUND = 4003;
    /** Código duplicado (code ya existe) */
    public const ERROR_CODE_DUPLICATE = 4102;
    /** No se puede eliminar: tiene clientes asociados */
    public const ERROR_TIENE_CLIENTES = 2115;

    /**
     * Listado paginado con búsqueda y filtros.
     *
     * @return array{items: array, total: int, page: int, page_size: int}
     */
    public function listado(int $page, int $pageSize, ?string $search, $activo, $inhabilitado, string $sort, string $sortDir): array
    {
        $sortWhitelist = ['code', 'descripcion', 'created_at', 'updated_at'];
        if (!in_array($sort, $sortWhitelist, true)) {
            $sort = 'descripcion';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        $query = TipoCliente::query()
            ->when($search !== null && trim($search) !== '', function ($q) use ($search) {
                $term = '%' . trim($search) . '%';
                $q->where(function ($q2) use ($term) {
                    $q2->where('code', 'like', $term)->orWhere('descripcion', 'like', $term);
                });
            })
            ->when($activo !== null && $activo !== '', function ($q) use ($activo) {
                $q->where('activo', filter_var($activo, FILTER_VALIDATE_BOOLEAN));
            })
            ->when($inhabilitado !== null && $inhabilitado !== '', function ($q) use ($inhabilitado) {
                $q->where('inhabilitado', filter_var($inhabilitado, FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy($sort, $sortDir);

        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);
        $items = $paginator->getCollection()->map(function (TipoCliente $t) {
            return [
                'id' => $t->id,
                'code' => $t->code,
                'descripcion' => $t->descripcion,
                'activo' => $t->activo,
                'inhabilitado' => $t->inhabilitado,
                'created_at' => $t->created_at?->toIso8601String(),
                'updated_at' => $t->updated_at?->toIso8601String(),
            ];
        })->values()->all();

        return [
            'items' => $items,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'page_size' => $paginator->perPage(),
        ];
    }

    /**
     * Obtener un tipo por ID.
     *
     * @throws \Exception ERROR_NOT_FOUND
     */
    public function getById(int $id): array
    {
        $tipo = TipoCliente::find($id);
        if (!$tipo) {
            throw new \Exception('Tipo de cliente no encontrado.', self::ERROR_NOT_FOUND);
        }
        return [
            'id' => $tipo->id,
            'code' => $tipo->code,
            'descripcion' => $tipo->descripcion,
            'activo' => $tipo->activo,
            'inhabilitado' => $tipo->inhabilitado,
            'created_at' => $tipo->created_at?->toIso8601String(),
            'updated_at' => $tipo->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Crear tipo de cliente. Código único.
     *
     * @throws ValidationException
     * @throws \Exception ERROR_CODE_DUPLICATE
     */
    public function create(array $data): array
    {
        $code = trim($data['code'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $activo = isset($data['activo']) ? (bool) $data['activo'] : true;
        $inhabilitado = isset($data['inhabilitado']) ? (bool) $data['inhabilitado'] : false;

        if ($code === '') {
            throw ValidationException::withMessages(['code' => ['El código es obligatorio.']]);
        }
        if ($descripcion === '') {
            throw ValidationException::withMessages(['descripcion' => ['La descripción es obligatoria.']]);
        }
        if (TipoCliente::where('code', $code)->exists()) {
            throw new \Exception('El código del tipo de cliente ya existe.', self::ERROR_CODE_DUPLICATE);
        }

        $tipo = TipoCliente::create([
            'code' => $code,
            'descripcion' => $descripcion,
            'activo' => $activo,
            'inhabilitado' => $inhabilitado,
        ]);

        return $this->getById($tipo->id);
    }

    /**
     * Actualizar tipo de cliente. Código no modificable.
     *
     * @throws \Exception ERROR_NOT_FOUND
     * @throws ValidationException
     */
    public function update(int $id, array $data): array
    {
        $tipo = TipoCliente::find($id);
        if (!$tipo) {
            throw new \Exception('Tipo de cliente no encontrado.', self::ERROR_NOT_FOUND);
        }

        $descripcion = trim($data['descripcion'] ?? '');
        $activo = isset($data['activo']) ? (bool) $data['activo'] : $tipo->activo;
        $inhabilitado = isset($data['inhabilitado']) ? (bool) $data['inhabilitado'] : $tipo->inhabilitado;

        if ($descripcion === '') {
            throw ValidationException::withMessages(['descripcion' => ['La descripción es obligatoria.']]);
        }

        $tipo->descripcion = $descripcion;
        $tipo->activo = $activo;
        $tipo->inhabilitado = $inhabilitado;
        $tipo->save();

        return $this->getById($tipo->id);
    }

    /**
     * Eliminar tipo de cliente. No permite si tiene clientes asociados.
     *
     * @throws \Exception ERROR_NOT_FOUND, ERROR_TIENE_CLIENTES
     */
    public function delete(int $id): void
    {
        $tipo = TipoCliente::find($id);
        if (!$tipo) {
            throw new \Exception('Tipo de cliente no encontrado.', self::ERROR_NOT_FOUND);
        }
        if ($tipo->clientes()->exists()) {
            throw new \Exception('No se puede eliminar el tipo de cliente porque tiene clientes asociados.', self::ERROR_TIENE_CLIENTES);
        }
        $tipo->delete();
    }
}
