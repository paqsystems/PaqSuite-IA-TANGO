# Endpoint: Crear Tipo de Tarea

## Información General

- **Método:** `POST`
- **Ruta:** `/api/v1/tipos-tarea`
- **Autenticación:** Requerida (Bearer Token)
- **Versión:** v1

---

## Descripción

Crea un nuevo tipo de tarea. Solo accesible para supervisores.

---

## Request

### Body

```json
{
  "code": "TIPO001",
  "descripcion": "Desarrollo",
  "is_generico": true,
  "is_default": false,
  "activo": true
}
```

### Parámetros

| Campo | Tipo | Requerido | Descripción | Validaciones |
|-------|------|-----------|-------------|--------------|
| `code` | string | Sí | Código único del tipo | No vacío, único (1105, 4101) |
| `descripcion` | string | Sí | Descripción del tipo | No vacío, máximo 200 caracteres |
| `is_generico` | boolean | No | Indica si es genérico | Default: false |
| `is_default` | boolean | No | Indica si es por defecto | Default: false. Si true, fuerza is_generico = true |
| `activo` | boolean | No | Estado activo | Default: true |

---

## Response

### Success (201 Created)

```json
{
  "error": 0,
  "respuesta": "Tipo de tarea creado correctamente",
  "resultado": {
    "id": 1,
    "code": "TIPO001",
    "descripcion": "Desarrollo",
    "is_generico": true,
    "is_default": false,
    "activo": true,
    "inhabilitado": false,
    "created_at": "2025-01-20T10:00:00Z"
  }
}
```

---

## Errores

### 422 Unprocessable Entity

```json
{
  "error": 2117,
  "respuesta": "Solo puede haber un tipo de tarea por defecto",
  "resultado": {}
}
```

**Códigos de error posibles:**
- `2117`: Solo puede haber un tipo de tarea por defecto

---

## Validaciones

### A Nivel de Negocio

1. **Regla de tipo por defecto:**
   - Si `is_default = true`, verificar que no haya otro tipo con `is_default = true`
   - Si `is_default = true`, forzar automáticamente `is_generico = true`

---

**Última actualización:** 2025-01-20

