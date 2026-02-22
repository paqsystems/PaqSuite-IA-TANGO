# Mapeo API a Datos — Sistema de Registro de Tareas

## Descripción General

Este documento mapea cada endpoint de la API a sus operaciones correspondientes en la base de datos, incluyendo:
- Tablas/entidades involucradas
- Operaciones CRUD realizadas
- Validaciones aplicadas
- Transformaciones de datos
- Relaciones cargadas
- Índices utilizados

---

## Convenciones

### Nomenclatura de Tablas

**Excepción:** La tabla `USERS` (sin prefijo PQ_PARTES_) es la única tabla que NO utiliza el prefijo. Es la tabla central de autenticación.

Todas las demás tablas físicas utilizan el prefijo obligatorio: `PQ_PARTES_`

**Mapeo lógico → físico:**
- `User` → `USERS` (sin prefijo)
- `Usuario` → `PQ_PARTES_USUARIOS`
- `Cliente` → `PQ_PARTES_CLIENTES`
- `TipoCliente` → `PQ_PARTES_TIPO_CLIENTE`
- `TipoTarea` → `PQ_PARTES_TIPO_TAREA`
- `RegistroTarea` → `PQ_PARTES_REGISTRO_TAREA`
- `ClienteTipoTarea` → `PQ_PARTES_CLIENTE_TIPO_TAREA`

### Operaciones ORM

- **Eloquent**: Operaciones usando Eloquent ORM
- **Query Builder**: Operaciones usando Query Builder de Laravel
- **Raw SQL**: SQL nativo cuando sea necesario (parametrizado)

---

## Endpoints de Autenticación

### POST /api/v1/auth/login

**Descripción:** Autentica un usuario contra la tabla `USERS` y genera un token de acceso. Después del login exitoso, determina si es Cliente o Usuario y obtiene los datos correspondientes.

**Tablas Involucradas:**
- `USERS` (sin prefijo PQ_PARTES_) - Tabla de autenticación
- `PQ_PARTES_CLIENTES` - Si el usuario es cliente
- `PQ_PARTES_USUARIOS` - Si el usuario es empleado/asistente

**Operaciones de Datos:**

1. **Validar código de usuario y contraseña en USERS:**
   ```php
   // Eloquent - Buscar en tabla USERS
   $user = User::where('code', $code)
       ->where('activo', true)
       ->where('inhabilitado', false)
       ->first();
   
   // Verificar contraseña
   if (!$user || !Hash::check($password, $user->password_hash)) {
       // Error 3201: Credenciales inválidas
   }
   ```

2. **Determinar tipo de usuario:**
   ```php
   $tipoUsuario = null;
   $clienteId = null;
   $usuarioId = null;
   $esSupervisor = false;
   $nombre = null;
   $email = null;
   
   // Buscar en PQ_PARTES_CLIENTES
   $cliente = Cliente::where('code', $user->code)
       ->where('activo', true)
       ->where('inhabilitado', false)
       ->first();
   
   if ($cliente) {
       $tipoUsuario = 'cliente';
       $clienteId = $cliente->id;
       $usuarioId = null;
       $esSupervisor = false;
       $nombre = $cliente->nombre;
       $email = $cliente->email;
   } else {
       // Buscar en PQ_PARTES_USUARIOS
       $usuario = Usuario::where('code', $user->code)
           ->where('activo', true)
           ->where('inhabilitado', false)
           ->first();
       
       if (!$usuario) {
           // Error 3202: Usuario no encontrado
       }
       
       $tipoUsuario = 'usuario';
       $clienteId = null;
       $usuarioId = $usuario->id;
       $esSupervisor = $usuario->supervisor;
       $nombre = $usuario->nombre;
       $email = $usuario->email;
   }
   ```

3. **Actualizar último login (opcional):**
   ```php
   $user->last_login_at = now();
   $user->save();
   ```

4. **Generar token Sanctum asociado al User:**
   ```php
   $token = $user->createToken('auth-token')->plainTextToken;
   ```

**Validaciones:**
- Código de usuario: no vacío (1102)
- Contraseña: no vacía (1103)
- User existe en `USERS` y está activo (3202, 4203)
- User.code debe existir en `PQ_PARTES_CLIENTES` O `PQ_PARTES_USUARIOS` (3202)

**Índices Utilizados:**
- `USERS.code` (UNIQUE)
- `USERS.activo`
- `PQ_PARTES_CLIENTES.code` (UNIQUE)
- `PQ_PARTES_USUARIOS.code` (UNIQUE)

**Response Mapping:**
```php
// Modelo → Response
[
    'token' => $token,
    'user' => [
        'user_id' => $user->id,
        'user_code' => $user->code,
        'tipo_usuario' => $tipoUsuario,
        'usuario_id' => $usuarioId,
        'cliente_id' => $clienteId,
        'es_supervisor' => $esSupervisor,
        'nombre' => $nombre,
        'email' => $email
    ]
]
```

**Valores a conservar durante el ciclo del proceso:**
Todos los campos de `resultado.user` deben conservarse durante todo el ciclo del proceso (desde login hasta logout) y estar disponibles en cada request autenticado. Ver `docs/modelo-datos.md` para más detalles.

**Campos Excluidos:**
- `password_hash` (nunca se expone)

---

## Endpoints de Catálogos

### GET /api/v1/clientes

**Descripción:** Obtiene la lista de clientes activos.

**Tablas Involucradas:**
- `PQ_PARTES_cliente`
- `PQ_PARTES_tipo_cliente` (JOIN para obtener tipo de cliente)

**Operaciones de Datos:**

```php
// Eloquent con relación tipo_cliente
$clientes = Cliente::where('activo', true)
    ->where('inhabilitado', false)
    ->with('tipoCliente:id,descripcion')
    ->orderBy('nombre', 'asc')
    ->get(['id', 'nombre', 'tipo_cliente_id', 'activo', 'inhabilitado']);
```

**Validaciones:**
- Usuario autenticado (3001)
- Token válido (3002)

**Índices Utilizados:**
- `idx_cliente_activo`

**Response Mapping:**
```php
// Modelo → Response
$clientes->map(function ($cliente) {
    return [
        'id' => $cliente->id,
        'nombre' => $cliente->nombre,
        'tipo_cliente' => [
            'id' => $cliente->tipoCliente->id,
            'descripcion' => $cliente->tipoCliente->descripcion
        ], // tipo_cliente_id es obligatorio, siempre existe
        'activo' => $cliente->activo,
        'inhabilitado' => (bool) $cliente->inhabilitado
    ];
});
```

**Filtros Opcionales:**
- `activo`: Filtrar por estado (default: true)

---

### GET /api/v1/tipos-tarea

**Descripción:** Obtiene la lista de tipos de tarea activos.

**Tablas Involucradas:**
- `PQ_PARTES_tipo_tarea`

**Operaciones de Datos:**

```php
// Eloquent
$tipos = TipoTarea::where('activo', true)
    ->where('inhabilitado', false)
    ->orderBy('descripcion', 'asc')
    ->get(['id', 'descripcion', 'is_generico', 'is_default', 'activo', 'inhabilitado']);
```

**Validaciones:**
- Usuario autenticado (3001)
- Token válido (3002)

**Índices Utilizados:**
- `idx_tipo_tarea_activo`
- `idx_tipo_tarea_generico` (para filtrar tipos genéricos)
- `idx_tipo_tarea_default` (para encontrar el tipo predeterminado)

**Response Mapping:**
```php
// Modelo → Response
$tipos->map(function ($tipo) {
    return [
        'id' => $tipo->id,
        'descripcion' => $tipo->descripcion,
        'is_generico' => (bool) $tipo->is_generico,
        'is_default' => (bool) $tipo->is_default,
        'activo' => $tipo->activo,
        'inhabilitado' => (bool) $tipo->inhabilitado
    ];
});
```

**Filtros Opcionales:**
- `activo`: Filtrar por estado (default: true)
- `is_generico`: Filtrar por tipos genéricos (pendiente implementación)
- `is_default`: Filtrar por tipo predeterminado (pendiente implementación)

**Notas:**
- Los campos `is_generico` e `is_default` están disponibles en el modelo pero las reglas de negocio asociadas se implementarán en una fase posterior.
- La tabla `PQ_PARTES_cliente_tipo_tarea` permite asociar tipos de tarea específicos a clientes (cuando `is_generico = false`).

---

## Endpoints de Tareas

### POST /api/v1/tareas

**Descripción:** Crea un nuevo registro de tarea.

**Tablas Involucradas:**
- `PQ_PARTES_registro_tarea` (INSERT)
- `PQ_PARTES_cliente` (SELECT para validación)
- `PQ_PARTES_tipo_tarea` (SELECT para validación)
- `PQ_PARTES_cliente_tipo_tarea` (SELECT para validación de tipos asociados al cliente - pendiente implementación)
- `PQ_PARTES_usuario` (obtener ID del usuario autenticado)

**Operaciones de Datos:**

1. **Validar cliente existe, está activo y no está inhabilitado:**
   ```php
   // Eloquent
   $cliente = Cliente::where('id', $request->cliente_id)
       ->where('activo', true)
       ->where('inhabilitado', false)
       ->first();
   
   if (!$cliente) {
       // Error 4003: Cliente no encontrado
       // o 4201: Cliente inactivo o inhabilitado
   }
   ```

2. **Validar tipo de tarea existe, está activo y no está inhabilitado:**
   ```php
   $tipoTarea = TipoTarea::where('id', $request->tipo_tarea_id)
       ->where('activo', true)
       ->where('inhabilitado', false)
       ->first();
   
   if (!$tipoTarea) {
       // Error 4004: Tipo de tarea no encontrado
       // o 4202: Tipo de tarea inactivo o inhabilitado
   }
   ```

3. **Validar reglas de negocio:**
   ```php
   // Fecha no futura
   if ($request->fecha > now()->format('Y-m-d')) {
       // Error 1203: Fecha futura no permitida
   }
   
   // Duración mayor a cero
   if ($request->duracion_minutos <= 0) {
       // Error 1207: Duración debe ser mayor a cero
   }
   ```

4. **Crear registro:**
   ```php
   // Eloquent
   $tarea = RegistroTarea::create([
       'usuario_id' => auth()->id(), // Del token
       'cliente_id' => $request->cliente_id,
       'tipo_tarea_id' => $request->tipo_tarea_id,
       'fecha' => $request->fecha,
       'duracion_minutos' => $request->duracion_minutos,
       'sin_cargo' => $request->sin_cargo ?? false,
       'presencial' => $request->presencial ?? false,
       'observacion' => $request->observacion ?? null,
       'created_at' => now(),
       'updated_at' => now()
   ]);
   ```

**Validaciones:**
- `fecha`: Requerido, formato YYYY-MM-DD, no futura (1201, 1202, 1203)
- `cliente_id`: Requerido, existe, activo (1204, 4003, 4201)
- `tipo_tarea_id`: Requerido, existe, activo (1205, 4004, 4202)
- `duracion_minutos`: Requerido, > 0, <= 1440 (1206, 1207, 1208)
- `observacion`: Opcional, max 1000 caracteres (1209)

**Índices Utilizados:**
- `idx_registro_usuario_fecha` (para consultas futuras)
- `idx_cliente_activo` (validación)
- `idx_tipo_tarea_activo` (validación)

**Response Mapping:**
```php
// Modelo → Response
[
    'id' => $tarea->id,
    'usuario_id' => $tarea->usuario_id,
    'cliente_id' => $tarea->cliente_id,
    'tipo_tarea_id' => $tarea->tipo_tarea_id,
    'fecha' => $tarea->fecha,
    'duracion_minutos' => $tarea->duracion_minutos,
    'observacion' => $tarea->observacion,
    'created_at' => $tarea->created_at->toISOString(),
    'updated_at' => $tarea->updated_at->toISOString()
]
```

---

### GET /api/v1/tareas

**Descripción:** Lista las tareas del usuario autenticado con paginación y filtros.

**Tablas Involucradas:**
- `PQ_PARTES_registro_tarea` (SELECT principal)
- `PQ_PARTES_cliente` (JOIN para nombre)
- `PQ_PARTES_tipo_tarea` (JOIN para descripción)

**Operaciones de Datos:**

```php
// Query Builder con JOIN (evitar N+1)
$query = RegistroTarea::query()
    ->where('usuario_id', auth()->id()) // Solo tareas del usuario
    ->leftJoin('PQ_PARTES_cliente', 'PQ_PARTES_registro_tarea.cliente_id', '=', 'PQ_PARTES_cliente.id')
    ->leftJoin('PQ_PARTES_tipo_cliente', 'PQ_PARTES_cliente.tipo_cliente_id', '=', 'PQ_PARTES_tipo_cliente.id')
    ->leftJoin('PQ_PARTES_tipo_tarea', 'PQ_PARTES_registro_tarea.tipo_tarea_id', '=', 'PQ_PARTES_tipo_tarea.id');

// Filtros opcionales
if ($request->has('fecha_desde')) {
    $query->where('PQ_PARTES_registro_tarea.fecha', '>=', $request->fecha_desde);
}

if ($request->has('fecha_hasta')) {
    $query->where('PQ_PARTES_registro_tarea.fecha', '<=', $request->fecha_hasta);
}

// Ordenamiento
$sortField = $request->get('sort', 'fecha');
$sortDir = $request->get('sort_dir', 'desc');
$query->orderBy("PQ_PARTES_registro_tarea.{$sortField}", $sortDir);

// Paginación
$page = $request->get('page', 1);
$pageSize = min($request->get('page_size', 20), 100);
$tareas = $query->paginate($pageSize, [
    'PQ_PARTES_registro_tarea.*',
    'PQ_PARTES_cliente.nombre as cliente_nombre',
    'PQ_PARTES_tipo_cliente.id as tipo_cliente_id',
    'PQ_PARTES_tipo_cliente.descripcion as tipo_cliente_descripcion',
    'PQ_PARTES_tipo_tarea.descripcion as tipo_tarea_descripcion'
], 'page', $page);
```

**Validaciones:**
- Usuario autenticado (3001)
- Token válido (3002)
- `page`: >= 1 (1301)
- `page_size`: 1-100 (1302)
- `sort`: Campo permitido (whitelist) (1303)
- `sort_dir`: 'asc' o 'desc' (1304)
- `fecha_desde` <= `fecha_hasta` (1305)

**Índices Utilizados:**
- `idx_registro_usuario_fecha` (principal)
- `idx_registro_fecha` (filtros por fecha)

**Response Mapping:**
```php
// Modelo → Response
[
    'items' => $tareas->map(function ($tarea) {
        return [
            'id' => $tarea->id,
            'fecha' => $tarea->fecha,
            'cliente' => [
                'id' => $tarea->cliente_id,
                'nombre' => $tarea->cliente_nombre,
                'tipo_cliente' => [
                    'id' => $tarea->tipo_cliente_id,
                    'descripcion' => $tarea->tipo_cliente_descripcion
                ] // tipo_cliente_id es obligatorio, siempre existe
            ],
            'tipo_tarea' => [
                'id' => $tarea->tipo_tarea_id,
                'descripcion' => $tarea->tipo_tarea_descripcion
            ],
            'duracion_minutos' => $tarea->duracion_minutos,
            'duracion_horas' => round($tarea->duracion_minutos / 60, 2),
            'sin_cargo' => (bool) $tarea->sin_cargo,
            'presencial' => (bool) $tarea->presencial,
            'observacion' => $tarea->observacion,
            'created_at' => $tarea->created_at->toISOString()
        ];
    }),
    'page' => $tareas->currentPage(),
    'page_size' => $tareas->perPage(),
    'total' => $tareas->total(),
    'total_pages' => $tareas->lastPage()
]
```

**Nota:** Se usa LEFT JOIN en lugar de subquery para mejor performance (ver regla en `03-data-access-orm-sql.md`).

---

### GET /api/v1/tareas/{id}

**Descripción:** Obtiene una tarea específica del usuario autenticado.

**Tablas Involucradas:**
- `PQ_PARTES_registro_tarea`
- `PQ_PARTES_cliente` (JOIN)
- `PQ_PARTES_tipo_tarea` (JOIN)

**Operaciones de Datos:**

```php
// Eloquent con eager loading
$tarea = RegistroTarea::where('id', $id)
    ->where('usuario_id', auth()->id()) // Solo tareas propias
    ->with(['cliente', 'tipoTarea'])
    ->first();

if (!$tarea) {
    // Error 4005: Tarea no encontrada
}
```

**Validaciones:**
- Usuario autenticado (3001)
- Token válido (3002)
- Tarea existe (4005)
- Tarea pertenece al usuario (3103)

**Índices Utilizados:**
- PRIMARY KEY (`id`)
- `idx_registro_usuario_fecha`

**Response Mapping:**
```php
// Similar a GET /api/v1/tareas pero sin paginación
```

---

### PUT /api/v1/tareas/{id}

**Descripción:** Actualiza una tarea existente del usuario autenticado.

**Tablas Involucradas:**
- `PQ_PARTES_registro_tarea` (UPDATE)
- `PQ_PARTES_cliente` (SELECT para validación)
- `PQ_PARTES_tipo_tarea` (SELECT para validación)

**Operaciones de Datos:**

1. **Verificar tarea existe y pertenece al usuario:**
   ```php
   $tarea = RegistroTarea::where('id', $id)
       ->where('usuario_id', auth()->id())
       ->first();
   
   if (!$tarea) {
       // Error 4005: Tarea no encontrada
       // o 2105: No se puede editar tarea de otro usuario
   }
   ```

2. **Validar cliente y tipo (si se modifican):**
   ```php
   // Similar a POST /api/v1/tareas
   ```

3. **Validar reglas de negocio:**
   ```php
   // Similar a POST /api/v1/tareas
   ```

4. **Actualizar:**
   ```php
   $updateData = [];
   if ($request->has('cliente_id')) $updateData['cliente_id'] = $request->cliente_id;
   if ($request->has('tipo_tarea_id')) $updateData['tipo_tarea_id'] = $request->tipo_tarea_id;
   if ($request->has('fecha')) $updateData['fecha'] = $request->fecha;
   if ($request->has('duracion_minutos')) $updateData['duracion_minutos'] = $request->duracion_minutos;
   if ($request->has('sin_cargo')) $updateData['sin_cargo'] = $request->sin_cargo;
   if ($request->has('presencial')) $updateData['presencial'] = $request->presencial;
   if ($request->has('observacion')) $updateData['observacion'] = $request->observacion;
   $updateData['updated_at'] = now();
   
   $tarea->update($updateData);
   ```

**Validaciones:**
- Mismas que POST /api/v1/tareas
- Tarea pertenece al usuario (2105)

**Índices Utilizados:**
- PRIMARY KEY (`id`)
- `idx_registro_usuario_fecha`

---

### DELETE /api/v1/tareas/{id}

**Descripción:** Elimina una tarea del usuario autenticado.

**Tablas Involucradas:**
- `PQ_PARTES_registro_tarea` (DELETE)

**Operaciones de Datos:**

```php
$tarea = RegistroTarea::where('id', $id)
    ->where('usuario_id', auth()->id())
    ->first();

if (!$tarea) {
    // Error 4005: Tarea no encontrada
    // o 2104: No se puede eliminar tarea de otro usuario
}

$tarea->delete();
```

**Validaciones:**
- Usuario autenticado (3001)
- Token válido (3002)
- Tarea existe (4005)
- Tarea pertenece al usuario (2104)

**Índices Utilizados:**
- PRIMARY KEY (`id`)
- `idx_registro_usuario_fecha`

---

### GET /api/v1/tareas/resumen

**Descripción:** Obtiene un resumen de dedicación por cliente del usuario autenticado.

**Tablas Involucradas:**
- `PQ_PARTES_registro_tarea` (SELECT con agregación)
- `PQ_PARTES_cliente` (JOIN)
- `PQ_PARTES_tipo_cliente` (JOIN para obtener tipo de cliente)

**Operaciones de Datos:**

```php
// Query Builder con agregación
$query = RegistroTarea::query()
    ->where('usuario_id', auth()->id())
    ->leftJoin('PQ_PARTES_cliente', 'PQ_PARTES_registro_tarea.cliente_id', '=', 'PQ_PARTES_cliente.id')
    ->leftJoin('PQ_PARTES_tipo_cliente', 'PQ_PARTES_cliente.tipo_cliente_id', '=', 'PQ_PARTES_tipo_cliente.id');

// Filtros opcionales
if ($request->has('fecha_desde')) {
    $query->where('PQ_PARTES_registro_tarea.fecha', '>=', $request->fecha_desde);
}

if ($request->has('fecha_hasta')) {
    $query->where('PQ_PARTES_registro_tarea.fecha', '<=', $request->fecha_hasta);
}

// Agrupación y agregación
$resumen = $query
    ->select([
        'PQ_PARTES_cliente.id as cliente_id',
        'PQ_PARTES_cliente.nombre as cliente_nombre',
        'PQ_PARTES_tipo_cliente.id as tipo_cliente_id',
        'PQ_PARTES_tipo_cliente.descripcion as tipo_cliente_descripcion',
        DB::raw('SUM(PQ_PARTES_registro_tarea.duracion_minutos) as total_minutos'),
        DB::raw('COUNT(PQ_PARTES_registro_tarea.id) as cantidad_tareas')
    ])
    ->groupBy(
        'PQ_PARTES_cliente.id', 
        'PQ_PARTES_cliente.nombre',
        'PQ_PARTES_tipo_cliente.id',
        'PQ_PARTES_tipo_cliente.descripcion'
    )
    ->orderBy('total_minutos', 'desc')
    ->get();

// Total general
$totalGeneral = RegistroTarea::where('usuario_id', auth()->id())
    ->when($request->has('fecha_desde'), function ($q) use ($request) {
        $q->where('fecha', '>=', $request->fecha_desde);
    })
    ->when($request->has('fecha_hasta'), function ($q) use ($request) {
        $q->where('fecha', '<=', $request->fecha_hasta);
    })
    ->sum('duracion_minutos');
```

**Validaciones:**
- Usuario autenticado (3001)
- Token válido (3002)
- `fecha_desde` <= `fecha_hasta` (1305)

**Índices Utilizados:**
- `idx_registro_usuario_fecha`
- `idx_registro_cliente_fecha`

**Response Mapping:**
```php
[
    'periodo' => [
        'fecha_desde' => $request->fecha_desde ?? null,
        'fecha_hasta' => $request->fecha_hasta ?? null
    ],
    'resumen_por_cliente' => $resumen->map(function ($item) {
        return [
            'cliente_id' => $item->cliente_id,
            'cliente_nombre' => $item->cliente_nombre,
            'tipo_cliente_id' => $item->tipo_cliente_id,
            'tipo_cliente_descripcion' => $item->tipo_cliente_descripcion,
            'total_minutos' => $item->total_minutos,
            'total_horas' => round($item->total_minutos / 60, 2),
            'cantidad_tareas' => $item->cantidad_tareas
        ];
    }),
    'totales' => [
        'total_minutos' => $totalGeneral,
        'total_horas' => round($totalGeneral / 60, 2),
        'cantidad_tareas' => RegistroTarea::where('usuario_id', auth()->id())
            ->when($request->has('fecha_desde'), function ($q) use ($request) {
                $q->where('fecha', '>=', $request->fecha_desde);
            })
            ->when($request->has('fecha_hasta'), function ($q) use ($request) {
                $q->where('fecha', '<=', $request->fecha_hasta);
            })
            ->count()
    ]
]
```

**Nota:** Se usa LEFT JOIN en lugar de subquery para verificar existencia de cliente.

---

## Resumen de Mapeos

### Tablas por Endpoint

| Endpoint | Tablas Principales | Operación |
|----------|-------------------|-----------|
| POST /auth/login | `USERS`, `PQ_PARTES_CLIENTES`, `PQ_PARTES_USUARIOS` | SELECT, UPDATE |
| GET /clientes | `PQ_PARTES_cliente`, `PQ_PARTES_tipo_cliente` | SELECT (JOIN) |
| GET /tipos-cliente | `PQ_PARTES_tipo_cliente` | SELECT |
| GET /tipos-tarea | `PQ_PARTES_tipo_tarea` | SELECT |
| POST /tareas | `PQ_PARTES_registro_tarea`, `PQ_PARTES_cliente`, `PQ_PARTES_tipo_tarea` | INSERT, SELECT |
| GET /tareas | `PQ_PARTES_registro_tarea`, `PQ_PARTES_cliente`, `PQ_PARTES_tipo_cliente`, `PQ_PARTES_tipo_tarea` | SELECT (JOIN) |
| GET /tareas/{id} | `PQ_PARTES_registro_tarea`, `PQ_PARTES_cliente`, `PQ_PARTES_tipo_cliente`, `PQ_PARTES_tipo_tarea` | SELECT (JOIN) |
| PUT /tareas/{id} | `PQ_PARTES_registro_tarea`, `PQ_PARTES_cliente`, `PQ_PARTES_tipo_tarea` | UPDATE, SELECT |
| DELETE /tareas/{id} | `PQ_PARTES_registro_tarea` | DELETE |
| GET /tareas/resumen | `PQ_PARTES_registro_tarea`, `PQ_PARTES_cliente`, `PQ_PARTES_tipo_cliente` | SELECT (JOIN + GROUP BY) |

### Índices Críticos

| Índice | Tabla | Uso |
|--------|-------|-----|
| `USERS.code` (UNIQUE) | `USERS` | Login, búsqueda |
| `USERS.activo` | `USERS` | Filtros |
| `USERS.inhabilitado` | `USERS` | Filtros, validaciones |
| `PQ_PARTES_CLIENTES.code` (UNIQUE) | `PQ_PARTES_CLIENTES` | Determinación tipo usuario |
| `PQ_PARTES_USUARIOS.code` (UNIQUE) | `PQ_PARTES_USUARIOS` | Determinación tipo usuario |
| `PQ_PARTES_USUARIOS.activo` | `PQ_PARTES_USUARIOS` | Filtros |
| `PQ_PARTES_USUARIOS.inhabilitado` | `PQ_PARTES_USUARIOS` | Filtros, validaciones |
| `idx_cliente_activo` | `PQ_PARTES_cliente` | Filtros, validaciones |
| `idx_cliente_inhabilitado` | `PQ_PARTES_cliente` | Filtros, validaciones |
| `idx_cliente_tipo_cliente` | `PQ_PARTES_cliente` | JOIN con tipo_cliente |
| `idx_tipo_cliente_activo` | `PQ_PARTES_tipo_cliente` | Filtros, validaciones |
| `idx_tipo_cliente_inhabilitado` | `PQ_PARTES_tipo_cliente` | Filtros, validaciones |
| `idx_tipo_tarea_activo` | `PQ_PARTES_tipo_tarea` | Filtros, validaciones |
| `idx_tipo_tarea_inhabilitado` | `PQ_PARTES_tipo_tarea` | Filtros, validaciones |
| `idx_tipo_tarea_generico` | `PQ_PARTES_tipo_tarea` | Filtros de tipos genéricos |
| `idx_tipo_tarea_default` | `PQ_PARTES_tipo_tarea` | Búsqueda del tipo predeterminado |
| `uk_cliente_tipo_tarea` | `PQ_PARTES_cliente_tipo_tarea` | Unique constraint (cliente_id, tipo_tarea_id) |
| `idx_cliente_tipo_tarea_cliente` | `PQ_PARTES_cliente_tipo_tarea` | Búsquedas por cliente |
| `idx_cliente_tipo_tarea_tipo` | `PQ_PARTES_cliente_tipo_tarea` | Búsquedas por tipo de tarea |
| `idx_registro_usuario_fecha` | `PQ_PARTES_registro_tarea` | Listado, resumen |
| `idx_registro_cliente_fecha` | `PQ_PARTES_registro_tarea` | Resumen por cliente |
| `idx_registro_fecha` | `PQ_PARTES_registro_tarea` | Filtros por fecha |

### Patrones de Consulta

1. **Validación de existencia:** LEFT JOIN + WHERE IS NOT NULL (no subqueries)
2. **Eager Loading:** Para evitar N+1 en relaciones
3. **Paginación:** Siempre en listados
4. **Filtros:** Whitelist de campos permitidos
5. **Agregaciones:** GROUP BY con JOIN, no subqueries

---

## Consideraciones de Performance

### Optimizaciones Aplicadas

1. **JOIN en lugar de subqueries:** Mejor uso de índices
2. **Eager loading:** Evita N+1 queries
3. **Índices compuestos:** Para consultas frecuentes (usuario_id + fecha)
4. **Paginación:** Limita resultados y mejora tiempos de respuesta
5. **Select específico:** Solo campos necesarios en JOINs

### Queries a Evitar

❌ **NO usar:**
```sql
-- Subquery en WHERE
SELECT * FROM clientes 
WHERE id IN (SELECT cliente_id FROM registro_tarea);
```

✅ **USAR:**
```sql
-- LEFT JOIN
SELECT clientes.*
FROM clientes
LEFT JOIN registro_tarea ON clientes.id = registro_tarea.cliente_id
WHERE registro_tarea.cliente_id IS NOT NULL;
```

---

## Notas

- Todos los endpoints (excepto login) requieren autenticación
- Todas las consultas de tareas filtran por `usuario_id` del token
- Los timestamps se manejan automáticamente (created_at, updated_at)
- Las validaciones se aplican tanto a nivel de API como de base de datos
- Los nombres de tablas físicas usan el prefijo `PQ_PARTES_`

---

**Última actualización:** 2025-01-20

