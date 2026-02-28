# Diagramas Mermaid – Base de Datos PQ_SUELD (Sueldos / Legajos)

Este archivo contiene los diagramas de entidad-relación en formato Mermaid para la base de datos **PQ_SUELD** (Company DB – Catálogo de tareas y legajos).

**Origen:** Los diagramas se derivan del modelo en `docs/modelo-datos/md-empresas/md-sueldos.md`.

**Archivos relacionados:**
- `md-sueldos.md` – Comandos SQL CREATE + definiciones de módulos
- `md-sueldos-diagramas.md` – Este archivo: diagramas Mermaid

---

## 1. Diagrama general (todas las tablas)

Vista consolidada de todas las entidades del módulo PQ_SUELD.

```mermaid
erDiagram
    PQ_SUELD_TAREAS {
        bigint ID PK
        nvarchar CODIGO UK
        nvarchar DESCRIPCION
    }

    PQ_SUELD_LEGAJOS {
        bigint ID PK
        int NRO_LEGAJO
        nvarchar APELLIDO
        nvarchar NOMBRE
        nvarchar CUIL
        bigint ID_USUARIO "FK lógica -> users.id (Dictionary DB)"
        bigint ID_SUELD_TAREA FK
    }

    PQ_SUELD_TAREAS ||--o{ PQ_SUELD_LEGAJOS : "ID_SUELD_TAREA"
```

---

## 2. Módulo CATÁLOGOS / MAESTROS

**Objetivo:** Catálogo de tareas (PQ_SUELD_TAREAS) y legajos/operarios (PQ_SUELD_LEGAJOS) para uso en módulos como Partes Producción.

**Relaciones:**
- 1 tarea → varios legajos (cada legajo tiene una tarea asignada)

```mermaid
erDiagram
    PQ_SUELD_TAREAS {
        bigint ID PK
        nvarchar CODIGO UK
        nvarchar DESCRIPCION
    }

    PQ_SUELD_LEGAJOS {
        bigint ID PK
        int NRO_LEGAJO
        nvarchar APELLIDO
        nvarchar NOMBRE
        nvarchar CUIL
        bigint ID_USUARIO
        bigint ID_SUELD_TAREA FK
    }

    PQ_SUELD_TAREAS ||--o{ PQ_SUELD_LEGAJOS : "ID_SUELD_TAREA"
```

---

## Resumen de módulos

| Módulo | Tablas | Estado relaciones |
|--------|--------|-------------------|
| **CATÁLOGOS** | PQ_SUELD_TAREAS, PQ_SUELD_LEGAJOS | Definidas |
