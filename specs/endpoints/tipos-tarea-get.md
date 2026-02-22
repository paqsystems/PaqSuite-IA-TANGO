# Endpoint: Obtener Tipo de Tarea

## Información General

- **Método:** `GET`
- **Ruta:** `/api/v1/tipos-tarea/{id}`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Obtiene el detalle de un tipo de tarea específico. Solo accesible para supervisores.

---

## Response

### Success (200 OK)

```json
{
  "error": 0,
  "respuesta": "Tipo de tarea obtenido correctamente",
  "resultado": {
    "id": 1,
    "code": "TIPO001",
    "descripcion": "Desarrollo",
    "is_generico": true,
    "is_default": false,
    "activo": true,
    "inhabilitado": false,
    "clientes_asignados": [
      {
        "id": 1,
        "code": "CLI001",
        "nombre": "Cliente A"
      }
    ],
    "created_at": "2025-01-15T10:00:00Z",
    "updated_at": "2025-01-15T10:00:00Z"
  }
}
```

---

## Notas

- Si el tipo NO es genérico, se incluyen los clientes asignados en `clientes_asignados`
- Si el tipo es genérico, `clientes_asignados` será un array vacío

---

**Última actualización:** 2025-01-20

