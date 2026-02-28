# Épica 002 – Grupos Empresarios

## Objetivo

Definir las historias de usuario para la administración de grupos empresarios: agrupaciones de empresas que permiten informes y procesos consolidados (operación multi-empresa).

## Tablas involucradas (Dictionary DB)

| Tabla | Propósito |
|-------|-----------|
| `PQ_GrupoEmpresario` | Grupos (id, descripcion) |
| `PQ_GrupoEmpresario_Empresas` | Asociación N:M grupo ↔ empresa |
| `PQ_Empresa` | Empresas disponibles para asignar |

## Reglas de negocio clave

- Un grupo puede contener varias empresas.
- Una empresa puede pertenecer a varios grupos.
- Un grupo NO puede existir sin al menos una empresa asignada.
- Los grupos se usan en reportes consolidados, parámetros de módulos (ej. Acopios) y procesos multi-empresa.

## Historias

| HU | Título | Clasificación |
|----|--------|---------------|
| [HU-001](HU-001-listado-grupos-empresarios.md) | Listado de grupos empresarios | MUST-HAVE |
| [HU-002](HU-002-creacion-grupo-empresario.md) | Creación de grupo empresario | MUST-HAVE |
| [HU-003](HU-003-edicion-grupo-empresario.md) | Edición de grupo empresario | MUST-HAVE |
| [HU-004](HU-004-eliminacion-grupo-empresario.md) | Eliminación de grupo empresario | MUST-HAVE |
| [HU-005](HU-005-detalle-grupo-empresario.md) | Detalle de grupo empresario | SHOULD-HAVE |

## Dependencias

```
Épica 001 (Seguridad) → HU-011 (Empresas) debe existir
HU-001 (Listado) → HU-002 (Creación) → HU-003 (Edición) → HU-004 (Eliminación)
```

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema PQ_GrupoEmpresario, PQ_GrupoEmpresario_Empresas
- `docs/modelo-datos/md-diccionario/md-diccionario-diagramas.md` – Diagrama módulo GRUPOS EMPRESARIOS
