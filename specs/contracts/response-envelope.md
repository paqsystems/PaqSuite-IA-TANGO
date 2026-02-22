# Contrato: Envelope de Respuesta

## Descripción General

Este documento define el formato estándar de respuesta (envelope) que todas las respuestas de la API deben seguir, independientemente de si son exitosas o contienen errores.

---

## Formato Estándar

Todas las respuestas HTTP (éxito o error) deben ser JSON con la siguiente estructura:

```json
{
  "error": 0,
  "respuesta": "mensaje para UI",
  "resultado": {}
}
```

---

## Campos del Envelope

### `error` (integer, requerido)

Código numérico que indica el resultado de la operación:

- **`0`**: Operación exitosa
- **`!= 0`**: Error controlado (validación, negocio, autorización, etc.)

**Regla:** El frontend **nunca** debe depender de formatos alternativos. Siempre debe verificar el campo `error`.

### `respuesta` (string, requerido)

Mensaje legible para el usuario, apto para mostrar directamente en la UI.

**Características:**
- En español
- Claro y conciso
- No debe exponer detalles técnicos internos
- Debe ser útil para el usuario final

**Ejemplos:**
- ✅ "Tarea registrada correctamente"
- ✅ "Credenciales inválidas"
- ❌ "SQLSTATE[23000]: Integrity constraint violation"
- ❌ "Error 500: Internal Server Error"

### `resultado` (object, requerido)

**Regla fundamental:** Debe ser siempre un objeto JSON. Si no hay datos para retornar, debe ser un objeto JSON vacío `{}`, nunca `null` ni ausente.

Contiene los datos de la respuesta. Puede ser:

- **Objeto complejo**: Para respuestas de un solo recurso
- **Array/colección**: Para listados (el array está dentro del objeto)
- **Objeto con metadata**: Para listados paginados
- **Objeto vacío `{}`**: Cuando no hay datos para retornar (éxito sin datos o errores sin información adicional)

**Ejemplos válidos:**
```json
// Objeto con datos
"resultado": { "id": 1, "nombre": "Ejemplo" }

// Array dentro del objeto
"resultado": { "items": [ {...}, {...} ] }

// Objeto vacío (sin datos)
"resultado": {}
```

**Ejemplos inválidos:**
```json
// ❌ INCORRECTO - null
"resultado": null

// ❌ INCORRECTO - Ausente
// (sin campo resultado)
```

---

## Ejemplos de Respuestas

### Éxito - Recurso Único

```json
{
  "error": 0,
  "respuesta": "Tarea obtenida correctamente",
  "resultado": {
    "id": 1,
    "fecha": "2025-01-20",
    "cliente_id": 1,
    "duracion_minutos": 120
  }
}
```

### Éxito - Lista Simple

```json
{
  "error": 0,
  "respuesta": "Clientes obtenidos correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "nombre": "Cliente A"
      },
      {
        "id": 2,
        "nombre": "Cliente B"
      }
    ]
  }
}
```

### Éxito - Lista Paginada

```json
{
  "error": 0,
  "respuesta": "Tareas obtenidas correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "fecha": "2025-01-20",
        "duracion_minutos": 120
      }
    ],
    "page": 1,
    "page_size": 20,
    "total": 1,
    "total_pages": 1
  }
}
```

### Error - Validación

```json
{
  "error": 1000,
  "respuesta": "Error de validación",
  "resultado": {
    "errors": {
      "fecha": ["La fecha no puede ser futura"],
      "duracion_minutos": ["La duración debe ser mayor a cero"]
    }
  }
}
```

### Error - Recurso No Encontrado

```json
{
  "error": 4005,
  "respuesta": "Tarea no encontrada",
  "resultado": {}
}
```

### Error - Autorización

```json
{
  "error": 3001,
  "respuesta": "Usuario no autenticado",
  "resultado": {}
}
```

---

## Códigos HTTP y Campo `error`

Aunque se usan códigos HTTP estándar, el cuerpo **siempre** mantiene el formato `error/respuesta/resultado`:

| Código HTTP | Campo `error` | Descripción |
|-------------|---------------|-------------|
| 200/201 | 0 | Operación exitosa |
| 400 | != 0 | Request inválido |
| 401 | 3000-3099 | No autenticado |
| 403 | 3100-3199 | No autorizado |
| 404 | 4000-4099 | Recurso no encontrado |
| 409 | 4100-4199 | Conflicto |
| 422 | 1000-1999, 2000-2999 | Validación o regla de negocio |
| 429 | 9004 | Rate limit excedido |
| 500 | 9999 | Error inesperado del servidor |

**Regla:** El frontend debe verificar el campo `error`, no solo el código HTTP.

---

## Estructura de Errores de Validación

Cuando hay múltiples errores de validación, se agrupan por campo:

```json
{
  "error": 1000,
  "respuesta": "Error de validación",
  "resultado": {
    "errors": {
      "campo1": ["Error 1 del campo", "Error 2 del campo"],
      "campo2": ["Error del campo 2"]
    }
  }
}
```

**Características:**
- Cada campo puede tener múltiples mensajes de error
- Los mensajes son arrays de strings
- Los campos sin errores no se incluyen

---

## Convenciones

### Mensajes de Éxito

- Deben ser afirmativos y claros
- Ejemplos: "Tarea registrada correctamente", "Usuario autenticado exitosamente"

### Mensajes de Error

- Deben ser descriptivos pero no técnicos
- No deben exponer detalles internos (stack traces, SQL, etc.)
- Deben guiar al usuario sobre cómo corregir el problema

### Campos Excluidos

Nunca se deben exponer en `resultado`:
- `password_hash` o cualquier hash de contraseña
- Tokens internos o secretos
- Detalles de configuración del servidor
- Stack traces o mensajes de error técnicos

---

## Versionado

El formato del envelope es estable y no cambia entre versiones de la API. Si se requiere un cambio en el formato, se debe crear una nueva versión de la API (ej: `/api/v2/...`).

---

## Implementación en Frontend

### Ejemplo de Manejo Genérico

```javascript
async function apiCall(url, options) {
  const response = await fetch(url, options);
  const data = await response.json();
  
  // Siempre verificar el campo 'error'
  if (data.error === 0) {
    return {
      success: true,
      data: data.resultado,
      message: data.respuesta
    };
  } else {
    return {
      success: false,
      error: data.error,
      message: data.respuesta,
      errors: data.resultado?.errors || null
    };
  }
}
```

### Ejemplo de Uso

```javascript
const result = await apiCall('/api/v1/tareas', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(tareaData)
});

if (result.success) {
  console.log('Éxito:', result.message);
  console.log('Datos:', result.data);
} else {
  console.error('Error:', result.message);
  if (result.errors) {
    // Mostrar errores de validación por campo
    Object.keys(result.errors).forEach(campo => {
      console.error(`${campo}:`, result.errors[campo]);
    });
  }
}
```

---

## Referencias

- Contrato base de API: `.cursor/rules/06-api-contract.md`
- Códigos de error: `specs/errors/domain-error-codes.md`

---

**Última actualización:** 2025-01-20

