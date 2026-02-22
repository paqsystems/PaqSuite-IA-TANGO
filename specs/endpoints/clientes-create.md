# Endpoint: Crear Cliente

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/clientes`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Crea un nuevo cliente en el sistema. Solo accesible para supervisores.

**Permisos:**
- **Solo supervisores** pueden crear clientes
- Si un usuario no supervisor intenta acceder, retornar error 403

---

## Request

### Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Body

```json
{
  "code": "CLI001",
  "nombre": "Cliente A",
  "tipo_cliente_id": 1,
  "email": "cliente@ejemplo.com",
  "password": "contraseña123",
  "activo": true
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción | Validaciones |
|-------|------|-----------|-------------|--------------|
| `code` | string | Sí | Código único del cliente | No vacío, único en la tabla (1105, 4101) |
| `nombre` | string | Sí | Nombre del cliente | No vacío, máximo 200 caracteres (1106, 1209) |
| `tipo_cliente_id` | integer | Sí | ID del tipo de cliente | Debe existir, estar activo y no inhabilitado (1107, 4007, 4205) |
| `email` | string | No | Email del cliente | Formato válido (1108), único si se proporciona (4102) |
| `password` | string | No | Contraseña para autenticación | Mínimo 8 caracteres si se proporciona (1104). Si se proporciona email, password es obligatorio |
| `activo` | boolean | No | Estado activo | Default: true |

---

## Response

### Success (201 Created)

```json
{
  "error": 0,
  "respuesta": "Cliente creado correctamente",
  "resultado": {
    "id": 1,
    "code": "CLI001",
    "nombre": "Cliente A",
    "tipo_cliente_id": 1,
    "email": "cliente@ejemplo.com",
    "activo": true,
    "inhabilitado": false,
    "created_at": "2025-01-20T10:00:00Z",
    "updated_at": "2025-01-20T10:00:00Z"
  }
}
```

### Campos de Respuesta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `error` | integer | Código de error (0 = éxito) |
| `respuesta` | string | Mensaje legible para el usuario |
| `resultado.id` | integer | ID del cliente creado |
| `resultado.code` | string | Código del cliente |
| `resultado.nombre` | string | Nombre del cliente |
| `resultado.tipo_cliente_id` | integer | ID del tipo de cliente |
| `resultado.email` | string\|null | Email del cliente |
| `resultado.activo` | boolean | Estado activo |
| `resultado.inhabilitado` | boolean | Estado inhabilitado (siempre false al crear) |

---

## Errores

### 403 Forbidden - No Autorizado

```json
{
  "error": 3101,
  "respuesta": "No tiene permisos para acceder a esta funcionalidad",
  "resultado": {}
}
```

### 422 Unprocessable Entity - Validación

```json
{
  "error": 1105,
  "respuesta": "El código del cliente ya existe",
  "resultado": {
    "errors": {
      "code": ["El código del cliente ya existe"]
    }
  }
}
```

**Códigos de error posibles:**
- `1105`: Código requerido o vacío
- `1106`: Nombre requerido o vacío
- `1107`: Tipo de cliente requerido
- `1108`: Email con formato inválido
- `1104`: Contraseña muy corta
- `4007`: Tipo de cliente no encontrado
- `4205`: Tipo de cliente inactivo
- `4101`: Código de cliente duplicado
- `4102`: Email duplicado
- `2116`: El cliente debe tener al menos un tipo de tarea disponible

---

## Validaciones

### A Nivel de Request

1. **Permisos:**
   - El usuario debe ser supervisor

2. **Campos obligatorios:**
   - `code`: No vacío, único
   - `nombre`: No vacío
   - `tipo_cliente_id`: Debe existir y estar activo/no inhabilitado

3. **Email y contraseña:**
   - Si se proporciona `email`, también debe proporcionarse `password` (y viceversa)
   - Si se proporciona `password`, debe tener mínimo 8 caracteres

### A Nivel de Negocio

1. **Regla de tipos de tarea:**
   - Después de crear el cliente, verificar que exista al menos un tipo de tarea genérico O el cliente tenga al menos un tipo asignado
   - Si no se cumple, retornar error 2116

---

## Operaciones de Base de Datos

### Tablas Involucradas

- `PQ_PARTES_cliente` (INSERT)
- `PQ_PARTES_tipo_cliente` (SELECT para validación)
- `PQ_PARTES_tipo_tarea` (SELECT para validar regla de tipos)

### Consultas

```php
// Validar tipo de cliente
$tipoCliente = TipoCliente::where('id', $request->tipo_cliente_id)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->firstOrFail();

// Crear cliente
$cliente = new Cliente();
$cliente->code = $request->code;
$cliente->nombre = $request->nombre;
$cliente->tipo_cliente_id = $request->tipo_cliente_id;
$cliente->email = $request->email;
$cliente->activo = $request->get('activo', true);
$cliente->inhabilitado = false;

if ($request->password) {
    $cliente->password_hash = Hash::make($request->password);
}

$cliente->save();

// Validar regla de tipos de tarea
$tiposGenericos = TipoTarea::where('is_generico', true)
    ->where('activo', true)
    ->where('inhabilitado', false)
    ->count();

if ($tiposGenericos === 0) {
    // El cliente debe tener al menos un tipo asignado
    // Esta validación se puede hacer después de asignar tipos
}
```

---

## Ejemplos de Uso

### cURL

```bash
curl -X POST "https://api.ejemplo.com/api/v1/clientes" \
  -H "Authorization: Bearer 1|abcdef1234567890" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "code": "CLI001",
    "nombre": "Cliente A",
    "tipo_cliente_id": 1,
    "email": "cliente@ejemplo.com",
    "password": "contraseña123",
    "activo": true
  }'
```

---

## Notas

- Solo supervisores pueden crear clientes
- El código debe ser único en todo el sistema
- Si se proporciona email, también debe proporcionarse password (para autenticación de cliente)
- Después de crear, se debe validar la regla de tipos de tarea (genéricos o asignados)

---

**Última actualización:** 2025-01-20

