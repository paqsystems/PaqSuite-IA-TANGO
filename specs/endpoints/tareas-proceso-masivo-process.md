# Endpoint: Procesar Tareas Masivamente

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/tareas/proceso-masivo`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Procesa masivamente las tareas seleccionadas, invirtiendo su estado (cerrado ↔ abierto). Solo accesible para supervisores.

**Permisos:**
- **Solo supervisores** pueden procesar tareas masivamente

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
  "tarea_ids": [1, 2, 3, 4, 5]
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción | Validaciones |
|-------|------|-----------|-------------|--------------|
| `tarea_ids` | array[integer] | Sí | IDs de las tareas a procesar | Al menos un ID, todos deben existir |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Se procesaron 5 registros",
  "resultado": {
    "procesadas": 5,
    "no_procesadas": 0,
    "detalle": [
      {
        "tarea_id": 1,
        "estado_anterior": false,
        "estado_nuevo": true,
        "procesada": true
      }
    ]
  }
}
```

---

## Errores

### 403 Forbidden

```json
{
  "error": 3101,
  "respuesta": "No tiene permisos para acceder a esta funcionalidad",
  "resultado": {}
}
```

### 422 Unprocessable Entity

```json
{
  "error": 2119,
  "respuesta": "Debe seleccionar al menos una tarea",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `2119`: Debe seleccionar al menos una tarea

---

## Validaciones

### A Nivel de Negocio

1. **Selección:**
   - Debe haber al menos un ID en `tarea_ids`
   - Todas las tareas deben existir

2. **Procesamiento:**
   - Invertir el estado `cerrado` de cada tarea
   - Si `cerrado = true`, cambiar a `false`
   - Si `cerrado = false`, cambiar a `true`
   - Procesamiento debe ser atómico (transaccional)

---

## Operaciones de Base de Datos

### Consultas

```php
DB::transaction(function () use ($tareaIds) {
    foreach ($tareaIds as $tareaId) {
        $tarea = RegistroTarea::findOrFail($tareaId);
        $tarea->cerrado = !$tarea->cerrado;
        $tarea->save();
    }
});
```

---

**Última actualización:** 2025-01-20

