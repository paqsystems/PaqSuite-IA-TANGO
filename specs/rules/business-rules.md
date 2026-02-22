# Reglas de Negocio Específicas

## Descripción General

Este documento define las reglas de negocio específicas del dominio que deben aplicarse en el sistema, más allá de las validaciones básicas de formato y tipo de datos.

---

## 1. Regla de Tipo de Tarea por Defecto

### Descripción
Solo puede existir un tipo de tarea con `is_default = true` en todo el sistema.

### Implementación
```php
// Al crear/actualizar un tipo de tarea con is_default = true
if ($request->is_default) {
    // Verificar que no haya otro tipo con is_default = true
    $otroDefault = TipoTarea::where('is_default', true)
        ->where('id', '!=', $id ?? 0)
        ->first();
    
    if ($otroDefault) {
        throw new BusinessRuleException('Solo puede haber un tipo de tarea por defecto', 2117);
    }
    
    // Forzar is_generico = true
    $request->merge(['is_generico' => true]);
}
```

### Código de Error
- **2117**: Solo puede haber un tipo de tarea por defecto

### Validación
- Aplicar al crear tipo de tarea con `is_default = true`
- Aplicar al actualizar tipo de tarea para establecer `is_default = true`
- Si se establece `is_default = true`, automáticamente forzar `is_generico = true`

---

## 2. Regla de Tipos Genéricos vs Específicos

### Descripción
- Los tipos de tarea con `is_generico = true` están disponibles para todos los clientes
- Los tipos de tarea con `is_generico = false` solo están disponibles para clientes que tengan una asociación explícita en la tabla `ClienteTipoTarea`

### Implementación
```php
// Al listar tipos de tarea disponibles para un cliente
$tiposGenericos = TipoTarea::where('is_generico', true)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->get();

$tiposEspecificos = TipoTarea::whereHas('clientes', function ($query) use ($clienteId) {
        $query->where('cliente_id', $clienteId);
    })
    ->where('is_generico', false)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->get();

$tiposDisponibles = $tiposGenericos->merge($tiposEspecificos);
```

### Validación
- Al crear/editar una tarea, solo mostrar tipos genéricos + tipos específicos asignados al cliente
- Al validar el tipo de tarea seleccionado, verificar que sea genérico o esté asignado al cliente

---

## 3. Regla de Cliente y Tipos de Tarea

### Descripción
Al crear/actualizar un cliente, debe existir al menos un tipo de tarea genérico O el cliente debe tener al menos un tipo de tarea asignado.

### Implementación
```php
// Al crear/actualizar cliente
$tiposGenericos = TipoTarea::where('is_generico', true)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->count();

$tiposAsignados = ClienteTipoTarea::where('cliente_id', $clienteId)->count();

if ($tiposGenericos === 0 && $tiposAsignados === 0) {
    throw new BusinessRuleException(
        'El cliente debe tener al menos un tipo de tarea genérico disponible o un tipo de tarea asignado',
        2116
    );
}
```

### Código de Error
- **2116**: El cliente debe tener al menos un tipo de tarea disponible

### Validación
- Aplicar después de crear/actualizar cliente
- Aplicar después de desasignar tipos de tarea de un cliente (si no quedan tipos genéricos)

---

## 4. Regla de Tarea Cerrada

### Descripción
Una tarea con `cerrado = true` no se puede modificar ni eliminar.

### Implementación
```php
// Al intentar actualizar tarea
if ($tarea->cerrado) {
    throw new BusinessRuleException('No se puede modificar una tarea cerrada', 2110);
}

// Al intentar eliminar tarea
if ($tarea->cerrado) {
    throw new BusinessRuleException('No se puede eliminar una tarea cerrada', 2111);
}
```

### Códigos de Error
- **2110**: No se puede modificar una tarea cerrada
- **2111**: No se puede eliminar una tarea cerrada

### Validación
- Aplicar antes de actualizar una tarea
- Aplicar antes de eliminar una tarea
- El campo `cerrado` solo puede ser modificado mediante el proceso masivo (supervisores)

---

## 5. Regla de Integridad Referencial

### Descripción
No se puede eliminar un Cliente, Usuario, TipoTarea o TipoCliente si están referenciados en otras tablas.

### Implementación

#### Cliente
```php
// Al intentar eliminar cliente
$tareasAsociadas = RegistroTarea::where('cliente_id', $clienteId)->count();
if ($tareasAsociadas > 0) {
    throw new BusinessRuleException(
        'No se puede eliminar un cliente que tiene tareas asociadas',
        2112
    );
}
```

#### Usuario/Asistente
```php
// Al intentar eliminar asistente
$tareasAsociadas = RegistroTarea::where('usuario_id', $usuarioId)->count();
if ($tareasAsociadas > 0) {
    throw new BusinessRuleException(
        'No se puede eliminar un asistente que tiene tareas asociadas',
        2113
    );
}
```

#### TipoTarea
```php
// Al intentar eliminar tipo de tarea
$tareasAsociadas = RegistroTarea::where('tipo_tarea_id', $tipoTareaId)->count();
$clientesAsociados = ClienteTipoTarea::where('tipo_tarea_id', $tipoTareaId)->count();
if ($tareasAsociadas > 0 || $clientesAsociados > 0) {
    throw new BusinessRuleException(
        'No se puede eliminar un tipo de tarea que está en uso',
        2114
    );
}
```

#### TipoCliente
```php
// Al intentar eliminar tipo de cliente
$clientesAsociados = Cliente::where('tipo_cliente_id', $tipoClienteId)->count();
if ($clientesAsociados > 0) {
    throw new BusinessRuleException(
        'No se puede eliminar un tipo de cliente que tiene clientes asociados',
        2115
    );
}
```

### Códigos de Error
- **2112**: No se puede eliminar un cliente con tareas asociadas
- **2113**: No se puede eliminar un asistente con tareas asociadas
- **2114**: No se puede eliminar un tipo de tarea en uso
- **2115**: No se puede eliminar un tipo de cliente con clientes asociados

### Validación
- Aplicar antes de eliminar cualquier entidad que tenga relaciones
- Verificar todas las tablas que referencian la entidad

---

## 6. Regla de Permisos por Rol

### Descripción
Los permisos de acceso y modificación de tareas dependen del rol del usuario.

### Implementación

#### Usuario Normal (supervisor = false)
```php
// Solo puede ver sus propias tareas
$tareas = RegistroTarea::where('usuario_id', auth()->id())
    ->get();

// Solo puede crear tareas para sí mismo
$tarea = new RegistroTarea();
$tarea->usuario_id = auth()->id(); // Forzar usuario autenticado
// ... otros campos

// Solo puede editar/eliminar sus propias tareas
$tarea = RegistroTarea::where('id', $id)
    ->where('usuario_id', auth()->id())
    ->firstOrFail();
```

#### Supervisor (supervisor = true)
```php
// Puede ver todas las tareas
$tareas = RegistroTarea::all();

// Puede crear tareas para cualquier usuario
$tarea = new RegistroTarea();
$tarea->usuario_id = $request->usuario_id ?? auth()->id(); // Puede seleccionar usuario
// ... otros campos

// Puede editar/eliminar cualquier tarea
$tarea = RegistroTarea::findOrFail($id);
```

#### Cliente
```php
// Solo puede ver tareas donde es el cliente
$tareas = RegistroTarea::where('cliente_id', auth()->id())
    ->get();

// No puede crear, editar ni eliminar tareas (solo lectura)
```

### Validación
- Aplicar en todos los endpoints de tareas
- Verificar rol del usuario autenticado
- Aplicar filtros automáticos según el rol

---

## 7. Regla de Filtros Automáticos según Tipo de Usuario

### Descripción
Los filtros se aplican automáticamente según el tipo de usuario, sin necesidad de que el usuario los especifique.

### Implementación

#### Usuario Cliente
```php
// Filtro automático: solo tareas donde cliente_id = usuario_autenticado.id
$query = RegistroTarea::where('cliente_id', auth()->id());

// No mostrar filtros de "cliente" ni "tipo de cliente" en la UI
// (ya está filtrado automáticamente)
```

#### Usuario No Supervisor
```php
// Filtro automático: solo tareas donde usuario_id = usuario_autenticado.id
$query = RegistroTarea::where('usuario_id', auth()->id());

// No mostrar filtro de "asistente" en la UI
// (ya está filtrado automáticamente)
```

#### Supervisor
```php
// Sin filtro automático: puede ver todas las tareas
$query = RegistroTarea::query();

// Mostrar todos los filtros en la UI
// (puede filtrar por asistente, cliente, tipo de cliente, etc.)
```

### Validación
- Aplicar en todos los endpoints de consulta/listado
- Ocultar filtros en la UI que ya están aplicados automáticamente
- Documentar en la especificación de endpoints qué filtros se aplican automáticamente

---

## 8. Regla de Duración en Tramos de 15 Minutos

### Descripción
La duración de las tareas debe estar en tramos de 15 minutos (15, 30, 45, 60, ..., 1440).

### Implementación
```php
// Validación de duración
if ($duracion_minutos % 15 !== 0) {
    throw new ValidationException(
        'La duración debe estar en tramos de 15 minutos',
        1210
    );
}

if ($duracion_minutos <= 0 || $duracion_minutos > 1440) {
    throw new ValidationException(
        'La duración debe ser mayor a cero y menor o igual a 24 horas',
        1207
    );
}
```

### Código de Error
- **1210**: Duración debe estar en tramos de 15 minutos
- **1207**: Duración debe ser mayor a cero

### Validación
- Aplicar al crear/actualizar tarea
- Valores válidos: 15, 30, 45, 60, 75, 90, ..., 1440

---

## 9. Regla de Fecha Futura (Advertencia)

### Descripción
Si la fecha de una tarea es futura, se muestra una advertencia pero no se bloquea la acción.

### Implementación
```php
// Validación de fecha futura (advertencia, no bloquea)
$fecha = Carbon::parse($request->fecha);
$warnings = [];

if ($fecha->isFuture()) {
    $warnings[] = [
        'campo' => 'fecha',
        'mensaje' => 'La fecha seleccionada es futura. ¿Desea continuar?',
        'codigo' => 1203
    ];
}

// Retornar advertencias en la respuesta, pero permitir continuar
return response()->json([
    'error' => 0,
    'respuesta' => 'Tarea registrada correctamente',
    'resultado' => $tarea,
    'advertencias' => $warnings // Opcional
]);
```

### Código de Error
- **1203**: Advertencia de fecha futura (no bloquea)

### Validación
- Aplicar al crear/actualizar tarea
- Mostrar advertencia en frontend, pero permitir continuar
- Opcionalmente, requerir confirmación del usuario

---

## 10. Regla de Observación Obligatoria

### Descripción
El campo `observacion` es obligatorio y no puede estar vacío.

### Implementación
```php
// Validación de observación
if (empty($request->observacion) || trim($request->observacion) === '') {
    throw new ValidationException(
        'El campo observación es obligatorio',
        1211
    );
}
```

### Código de Error
- **1211**: El campo observación es obligatorio

### Validación
- Aplicar al crear/actualizar tarea
- El campo no puede estar vacío ni contener solo espacios en blanco

---

## Orden de Aplicación de Reglas

Las reglas de negocio se aplican en el siguiente orden:

1. **Validaciones de formato y tipo** (validation-rules.md)
2. **Validaciones de existencia y estado** (recursos existen y están activos)
3. **Reglas de negocio específicas** (este documento)
4. **Validaciones de permisos** (autorización)
5. **Operaciones de base de datos** (transacciones)

---

## Referencias

- Validaciones básicas: `specs/rules/validation-rules.md`
- Códigos de error: `specs/errors/domain-error-codes.md`
- Modelo de datos: `docs/modelo-datos.md`
- Reglas de negocio completas: `docs/reglas-negocio.md`

---

**Última actualización:** 2025-01-20

