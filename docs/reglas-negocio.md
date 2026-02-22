# Reglas de Negocio - Sistema de Registro de Tareas

Este documento consolida todas las reglas de negocio del sistema, organizadas por entidad y funcionalidad.

---

## 1. Autenticación y Login

### 1.1 Validaciones de Login
- **Validaciones estándar:** Todas las validaciones estándar de un login (código no vacío, contraseña no vacía, formato válido)
- **Usuario activo:** El usuario debe estar activo (`activo = true`)
- **Usuario no inhabilitado:** El usuario no debe estar inhabilitado (`inhabilitado = false`)
- **Código asociado:** El usuario debe tener asociado un código de cliente o empleado (según el tipo de usuario)

**Implementación:**
- Verificar en tabla `PQ_PARTES_usuario` o `PQ_PARTES_cliente` según el tipo de autenticación
- Validar `activo = true` y `inhabilitado = false`
- Validar que `code` no sea NULL

---

## 2. Cliente

### 2.1 Campos Obligatorios al Cargar Cliente
- **Código:** Obligatorio (único)
- **Descripción:** Obligatorio (campo `nombre` en el modelo)
- **Tipo de Cliente:** Obligatorio (`tipo_cliente_id` NOT NULL)
- **Inhabilitado:** Atributo booleano (default: false)

### 2.2 Validaciones de Tipo de Cliente
- El tipo de cliente asignado debe estar habilitado:
  - `activo = true`
  - `inhabilitado = false`

### 2.3 Regla de Tipos de Tarea para Cliente
- **Al cargar un cliente:** Debe existir por lo menos un tipo de tarea genérico (`is_generico = true`) O el cliente debe tener asignado al menos un tipo de tarea específico en la tabla `ClienteTipoTarea`
- **Propósito:** Garantizar que el cliente pueda tener tareas registradas

**Implementación:**
```php
// Al crear/actualizar cliente, verificar:
$tiposGenericos = TipoTarea::where('is_generico', true)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->count();

$tiposAsignados = ClienteTipoTarea::where('cliente_id', $clienteId)->count();

if ($tiposGenericos === 0 && $tiposAsignados === 0) {
    throw new BusinessRuleException('El cliente debe tener al menos un tipo de tarea genérico disponible o un tipo de tarea asignado');
}
```

---

## 3. Empleado

### 3.1 Campos Obligatorios al Cargar Empleado
- **Código:** Obligatorio (único) - Campo `code`
- **Descripción:** Obligatorio - Campo `nombre`
- **Inhabilitado:** Atributo booleano (default: false)

---

## 4. Tipo de Tarea

### 4.1 Campos Obligatorios
- **Código:** Obligatorio (único) - **NOTA:** Requiere agregar campo `code` al modelo
- **Descripción:** Obligatorio - Campo `descripcion`
- **Inhabilitado:** Atributo booleano (default: false)

### 4.2 Regla de Tipo de Tarea por Defecto
- **Solo uno por defecto:** Solo puede haber un tipo de tarea con el atributo `is_default = true` en todo el sistema
- **Forzar genérico:** Si un tipo de tarea tiene `is_default = true`, debe forzar automáticamente `is_generico = true`
- **Propósito:** Garantizar que siempre haya un tipo de tarea predeterminado y que sea accesible para todos los clientes

**Implementación:**
```php
// Al crear/actualizar tipo de tarea con is_default = true:
if ($request->is_default) {
    // 1. Verificar que no haya otro tipo con is_default = true
    $otroDefault = TipoTarea::where('is_default', true)
        ->where('id', '!=', $id ?? 0)
        ->first();
    
    if ($otroDefault) {
        throw new BusinessRuleException('Solo puede haber un tipo de tarea por defecto');
    }
    
    // 2. Forzar is_generico = true
    $request->merge(['is_generico' => true]);
}
```

---

## 5. Tipo de Cliente

### 5.1 Campos Obligatorios
- **Código:** Obligatorio (único) - **NOTA:** Requiere agregar campo `code` al modelo
- **Descripción:** Obligatorio - Campo `descripcion`
- **Inhabilitado:** Atributo booleano (default: false)

---

## 6. Tarea (RegistroTarea)

### 6.1 Validaciones Obligatorias
Una tarea debe verificar que:

1. **Código de empleado:** Contenga un `usuario_id` válido (obligatorio)
2. **Código de cliente:** Contenga un `cliente_id` válido (obligatorio)
3. **Tipo de tarea:** Contenga un `tipo_tarea_id` válido (obligatorio)
4. **Fecha válida:** La fecha sea una fecha válida en formato YYYY-MM-DD
5. **Fecha futura (advertencia):** Si la fecha es mayor a hoy, presenta un mensaje de **advertencia** (no bloquea la creación)
6. **Duración en tramos de 15 minutos:** La duración debe estar en tramos de 15 minutos (0, 15, 30, 45, 60, 75, 90, ..., 1440), ser mayor a cero y menor/igual a 24 horas (1440 minutos)
7. **Descripción no vacía:** El campo `observacion` no debe estar vacío (obligatorio, no opcional)
8. **Sin cargo no null:** El atributo `sin_cargo` no debe estar null (iniciar por defecto en `false`)
9. **Presencial no null:** El atributo `presencial` no debe estar null (iniciar por defecto en `false`)

### 6.2 Validación de Atributos Inhabilitados
- **No mostrar inhabilitados:** Que no aparezca para asignar a una tarea ningún atributo con estado `inhabilitado = true`:
  - Cliente (`inhabilitado = false`)
  - Empleado (`inhabilitado = false`)
  - Tipo de tarea (`inhabilitado = false`)

**Implementación en selects:**
```php
// Al listar clientes para select:
$clientes = Cliente::where('activo', true)
    ->where('inhabilitado', false)
    ->get();

// Al listar tipos de tarea para select:
$tiposTarea = TipoTarea::where('activo', true)
    ->where('inhabilitado', false)
    ->get();

// Al listar empleados para select (solo supervisores):
$empleados = Usuario::where('activo', true)
    ->where('inhabilitado', false)
    ->get();
```

### 6.3 Validación de Duración en Tramos de 15 Minutos
- **Regla:** `duracion_minutos % 15 === 0`
- **Rango:** `0 < duracion_minutos <= 1440`
- **Valores válidos:** 15, 30, 45, 60, 75, 90, 105, ..., 1440

**Implementación:**
```php
if ($duracion_minutos % 15 !== 0) {
    throw new ValidationException('La duración debe estar en tramos de 15 minutos', 1210);
}

if ($duracion_minutos <= 0 || $duracion_minutos > 1440) {
    throw new ValidationException('La duración debe ser mayor a cero y menor o igual a 24 horas', 1207);
}
```

### 6.4 Estado "Cerrado" de Tarea
- **Campo requerido:** Agregar campo `cerrado` (boolean, default: false) a la tabla `RegistroTarea`
- **Regla de modificación:** Una tarea no se puede modificar ni eliminar si está en estado "cerrado" (`cerrado = true`)

**Implementación:**
```php
// Al intentar actualizar tarea:
if ($tarea->cerrado) {
    throw new BusinessRuleException('No se puede modificar una tarea cerrada', 2110);
}

// Al intentar eliminar tarea:
if ($tarea->cerrado) {
    throw new BusinessRuleException('No se puede eliminar una tarea cerrada', 2111);
}
```

---

## 7. Proceso Masivo de Tareas

### 7.1 Validación de Supervisor
- **Permiso requerido:** Verificar que el usuario que quiere procesar sea supervisor (`supervisor = true`)
- **Acceso denegado:** Si el usuario no es supervisor, mostrar error 403

### 7.2 Validación de Botón de Procesar
- **Botón deshabilitado:** El botón de procesar NO se debe activar si no hay ningún registro activo seleccionado
- **Validación:** Al menos una tarea debe estar seleccionada para habilitar el botón

**Implementación en frontend:**
```typescript
const canProcess = selectedTasks.length > 0 && selectedTasks.some(task => !task.cerrado);
```

---

## 8. Informes y Consultas

### 8.1 Validación de Período
- **Rango válido:** Verificar que el período sea correcto (`fecha_desde <= fecha_hasta`)
- **Código de error:** 1305

### 8.2 Restricciones por Tipo de Usuario

#### 8.2.1 Usuario Cliente
- **Filtro automático:** Si el usuario es cliente, solo puede ver las tareas que se le realizaron a él (`cliente_id = usuario_autenticado.id`)
- **Filtros ocultos:** No debe aparecer "cliente" ni "tipo de cliente" como posibilidad de filtro (ya está filtrado automáticamente)

#### 8.2.2 Usuario No Supervisor
- **Filtro automático:** Si el usuario no es un empleado supervisor, solo puede ver las tareas que realizó (`usuario_id = usuario_autenticado.id`)
- **Filtros ocultos:** No debe aparecer la opción "empleado" como posibilidad de filtro (ya está filtrado automáticamente)

### 8.3 Resultado Vacío
- **Mensaje informativo:** Si el resultado de la obtención de datos es vacío, avisar al usuario
- **Ocultar elementos:** No presentar la lista ni habilitar el botón para exportar a Excel

**Implementación:**
```php
if ($tareas->isEmpty()) {
    return response()->json([
        'error' => 0,
        'respuesta' => 'No se encontraron tareas para los filtros seleccionados',
        'resultado' => []
    ]);
}
```

---

## 9. Integridad Referencial

### 9.1 Regla de Eliminación
- **No eliminar si está referenciado:** No se puede eliminar un cliente, empleado, tipo de tarea ni tipo de cliente, si están referenciados en otras tablas

**Implementación:**
```php
// Al intentar eliminar cliente:
$tareasAsociadas = RegistroTarea::where('cliente_id', $clienteId)->count();
if ($tareasAsociadas > 0) {
    throw new BusinessRuleException('No se puede eliminar un cliente que tiene tareas asociadas', 2112);
}

// Al intentar eliminar empleado:
$tareasAsociadas = RegistroTarea::where('usuario_id', $usuarioId)->count();
if ($tareasAsociadas > 0) {
    throw new BusinessRuleException('No se puede eliminar un empleado que tiene tareas asociadas', 2113);
}

// Al intentar eliminar tipo de tarea:
$tareasAsociadas = RegistroTarea::where('tipo_tarea_id', $tipoTareaId)->count();
$clientesAsociados = ClienteTipoTarea::where('tipo_tarea_id', $tipoTareaId)->count();
if ($tareasAsociadas > 0 || $clientesAsociados > 0) {
    throw new BusinessRuleException('No se puede eliminar un tipo de tarea que está en uso', 2114);
}

// Al intentar eliminar tipo de cliente:
$clientesAsociados = Cliente::where('tipo_cliente_id', $tipoClienteId)->count();
if ($clientesAsociados > 0) {
    throw new BusinessRuleException('No se puede eliminar un tipo de cliente que tiene clientes asociados', 2115);
}
```

---

## 10. Resumen de Cambios Requeridos en el Modelo de Datos

### 10.1 Campos a Agregar

1. **RegistroTarea:**
   - `cerrado` (boolean, default: false) - Indica si la tarea está cerrada

2. **TipoTarea:**
   - `code` (string, único, obligatorio) - Código del tipo de tarea

3. **TipoCliente:**
   - `code` (string, único, obligatorio) - Código del tipo de cliente

4. **Cliente:**
   - `code` debe ser obligatorio (cambiar de opcional a NOT NULL)

### 10.2 Campos a Modificar

1. **RegistroTarea:**
   - `observacion` debe ser obligatorio (cambiar de opcional a NOT NULL)

---

## 11. Códigos de Error Adicionales Requeridos

| Código | Descripción | HTTP | Contexto |
|--------|-------------|------|----------|
| 1210 | Duración debe estar en tramos de 15 minutos | 422 | Validación de duración |
| 2110 | No se puede modificar una tarea cerrada | 403 | Edición de tarea |
| 2111 | No se puede eliminar una tarea cerrada | 403 | Eliminación de tarea |
| 2112 | No se puede eliminar un cliente con tareas asociadas | 422 | Eliminación de cliente |
| 2113 | No se puede eliminar un empleado con tareas asociadas | 422 | Eliminación de empleado |
| 2114 | No se puede eliminar un tipo de tarea en uso | 422 | Eliminación de tipo de tarea |
| 2115 | No se puede eliminar un tipo de cliente con clientes asociados | 422 | Eliminación de tipo de cliente |
| 2116 | El cliente debe tener al menos un tipo de tarea disponible | 422 | Creación/actualización de cliente |
| 2117 | Solo puede haber un tipo de tarea por defecto | 422 | Creación/actualización de tipo de tarea |

---

## 12. Referencias

- Modelo de datos: `docs/modelo-datos.md`
- Reglas de validación: `specs/rules/validation-rules.md`
- Códigos de error: `specs/errors/domain-error-codes.md`
- Especificaciones de endpoints: `specs/endpoints/`

---

**Última actualización:** 2025-01-20

