# Endpoint: Obtener Registro de Tarea

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/tareas/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene el detalle de un registro de tarea específico. Los usuarios normales solo pueden ver sus propias tareas. Los supervisores pueden ver cualquier tarea.

**Permisos:**
- **Usuario normal:** Solo puede ver sus propias tareas
- **Supervisor:** Puede ver cualquier tarea

---

## Request

### Headers

```
Authorization: Bearer {token}
Accept: application/json
```

### Path Parameters

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `id` | integer | Sí | ID del registro de tarea |

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tarea obtenida correctamente",
  "resultado": {
    "id": 1,
    "usuario": {
      "id": 1,
      "code": "JPEREZ",
      "nombre": "Juan Pérez"
    },
    "cliente": {
      "id": 1,
      "code": "CLI001",
      "nombre": "Cliente A",
      "tipo_cliente": {
        "id": 1,
        "descripcion": "Corporativo"
      }
    },
    "tipo_tarea": {
      "id": 1,
      "code": "TIPO001",
      "descripcion": "Desarrollo"
    },
    "fecha": "2025-01-20",
    "duracion_minutos": 120,
    "duracion_horas": 2.0,
    "sin_cargo": false,
    "presencial": false,
    "observacion": "Desarrollo de funcionalidad X",
    "cerrado": false,
    "created_at": "2025-01-20T10:30:00Z",
    "updated_at": "2025-01-20T10:30:00Z"
  }
}
```

---

## Errores

### 404 Not Found

```json
{
  "error": 4005,
  "respuesta": "Tarea no encontrada",
  "resultado": {}
}
```

### 403 Forbidden - No Autorizado

```json
{
  "error": 3102,
  "respuesta": "No tiene permisos para ver esta tarea",
  "resultado": {}
}
```

---

## Validaciones

### A Nivel de Request

1. **Permisos:**
   - Usuario normal: Solo puede ver tareas donde `usuario_id = usuario_autenticado.id`
   - Supervisor: Puede ver cualquier tarea

---

**Última actualización:** 2025-01-20

