# Diagramas Mermaid – Base de Datos PQ_PRD (Partes Producción)

Este archivo contiene los diagramas de entidad-relación en formato Mermaid para la base de datos **PQ_PRD** (Company DB – Gestión de Tareas + Eficiencia Operaria).

**Origen:** Los diagramas se derivan del modelo propuesto en `docs/_projects/PartesProduccion/modelo-datos-propuesto.md`.

**Archivos relacionados:**
- `md-partes-produccion.md` – Comandos SQL CREATE + definiciones de módulos
- `md-partes-produccion-diagramas.md` – Este archivo: diagramas Mermaid

---

## 1. Diagrama general (todas las tablas)

Vista consolidada de todas las entidades del módulo PQ_PRD.

```mermaid
erDiagram
    PQ_PRD_ORDENES_TRABAJO {
        bigint ID_ORDEN_TRABAJO PK
        nvarchar CODIGO_OT UK
        nvarchar TIPO_REF_EXTERNA
        nvarchar ID_REF_EXTERNA
        nvarchar DESCRIPCION
        bigint ID_ARTICULO
        date FECHA_INICIO_PLAN
        date FECHA_FIN_PLAN
        tinyint ESTADO "0=Borrador 1=Abierta 2=Cerrada 3=Anulada"
        datetime2 FECHA_ALTA
        bigint USUARIO_ALTA
    }

    PQ_PRD_OPERACIONES {
        int ID_OPERACION PK
        nvarchar CODIGO_OPERACION UK
        nvarchar NOMBRE
        bit ACTIVA
    }

    PQ_PRD_ARTICULO_OPERACION_STD {
        bigint ID_ARTICULO_OPERACION_STD PK
        bigint ID_ARTICULO
        int ID_OPERACION FK
        decimal UNIDADES_HORA_STD
        int MINUTOS_PREPARACION_STD
        bit ACTIVO
    }

    PQ_PRD_MAQUINAS {
        int ID_MAQUINA PK
        nvarchar CODIGO_MAQUINA UK
        nvarchar NOMBRE
        bit ACTIVA
    }

    PQ_PRD_TIPOS_TAREA {
        int ID_TIPO_TAREA PK
        nvarchar CODIGO_TIPO_TAREA UK
        nvarchar NOMBRE
        bit ACTIVO
    }

    PQ_PRD_CONCEPTOS_TIEMPO {
        int ID_CONCEPTO_TIEMPO PK
        nvarchar CODIGO_CONCEPTO UK
        nvarchar NOMBRE
        bit ES_PRODUCTIVO
        bit ACTIVO
    }

    PQ_PRD_TURNOS {
        int ID_TURNO PK
        nvarchar CODIGO_TURNO UK
        nvarchar NOMBRE
        time HORA_INICIO
        time HORA_FIN
        bit ACTIVO
    }

    PQ_PRD_ASIGNACIONES {
        bigint ID_ASIGNACION PK
        date FECHA_ASIGNACION
        int ID_TURNO FK
        bigint ID_USUARIO_SUPERVISOR
        tinyint ESTADO "0=Borrador 1=Publicada 2=Cerrada 3=Anulada"
        datetime2 FECHA_PUBLICACION
        datetime2 FECHA_CIERRE
    }

    PQ_PRD_ASIGNACIONES_ITEMS {
        bigint ID_ASIGNACION_ITEM PK
        bigint ID_ASIGNACION FK
        bigint ID_ORDEN_TRABAJO FK
        bigint ID_ARTICULO
        int ID_OPERACION FK
        int ID_TIPO_TAREA FK
        int ID_MAQUINA FK
        decimal UNIDADES_HORA_STD "snapshot"
        decimal UNIDADES_PLAN
        int MINUTOS_PLAN
        bit ACTIVO
    }

    PQ_PRD_ASIGNACIONES_ITEMS_OPERARIOS {
        bigint ID_ASIGITEM_OPERARIO PK
        bigint ID_ASIGNACION_ITEM FK
        bigint ID_OPERARIO
    }

    PQ_PRD_PARTES_OPERARIO {
        bigint ID_PARTE_OPERARIO PK
        date FECHA_PARTE
        int ID_TURNO
        bigint ID_OPERARIO
        tinyint ESTADO "0=Abierto 1=Enviado 2=Revisado 3=Bloqueado"
    }

    PQ_PRD_PARTES_ENTRADAS {
        bigint ID_PARTE_ENTRADA PK
        bigint ID_PARTE_OPERARIO FK
        bigint ID_ASIGNACION_ITEM FK
        bigint ID_ORDEN_TRABAJO FK
        int ID_CONCEPTO_TIEMPO FK
        int MINUTOS
        decimal UNIDADES_HECHAS
        decimal UNIDADES_MERMA
    }

    PQ_PRD_ORDENES_TRABAJO ||--o{ PQ_PRD_ASIGNACIONES_ITEMS : "ID_ORDEN_TRABAJO"
    PQ_PRD_OPERACIONES ||--o{ PQ_PRD_ARTICULO_OPERACION_STD : "ID_OPERACION"
    PQ_PRD_OPERACIONES ||--o{ PQ_PRD_ASIGNACIONES_ITEMS : "ID_OPERACION"
    PQ_PRD_OPERACIONES ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_OPERACION"
    PQ_PRD_MAQUINAS ||--o{ PQ_PRD_ASIGNACIONES_ITEMS : "ID_MAQUINA"
    PQ_PRD_MAQUINAS ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_MAQUINA"
    PQ_PRD_TIPOS_TAREA ||--o{ PQ_PRD_ASIGNACIONES_ITEMS : "ID_TIPO_TAREA"
    PQ_PRD_TIPOS_TAREA ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_TIPO_TAREA"
    PQ_PRD_CONCEPTOS_TIEMPO ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_CONCEPTO_TIEMPO"
    PQ_PRD_TURNOS ||--o{ PQ_PRD_ASIGNACIONES : "ID_TURNO"
    PQ_PRD_ASIGNACIONES ||--o{ PQ_PRD_ASIGNACIONES_ITEMS : "ID_ASIGNACION"
    PQ_PRD_ASIGNACIONES_ITEMS ||--o{ PQ_PRD_ASIGNACIONES_ITEMS_OPERARIOS : "ID_ASIGNACION_ITEM"
    PQ_PRD_ASIGNACIONES_ITEMS ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_ASIGNACION_ITEM"
    PQ_PRD_ORDENES_TRABAJO ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_ORDEN_TRABAJO"
    PQ_PRD_PARTES_OPERARIO ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_PARTE_OPERARIO"
```

---

## 2. Módulo CATÁLOGOS / MAESTROS

**Objetivo:** Órdenes de trabajo, operaciones, estándares artículo-operación, máquinas, tipos de tarea, conceptos de tiempo y turnos.

```mermaid
erDiagram
    PQ_PRD_ORDENES_TRABAJO {
        bigint ID_ORDEN_TRABAJO PK
        nvarchar CODIGO_OT UK
        nvarchar TIPO_REF_EXTERNA
        bigint ID_ARTICULO
        tinyint ESTADO
    }

    PQ_PRD_OPERACIONES {
        int ID_OPERACION PK
        nvarchar CODIGO_OPERACION UK
        nvarchar NOMBRE
        bit ACTIVA
    }

    PQ_PRD_ARTICULO_OPERACION_STD {
        bigint ID_ARTICULO_OPERACION_STD PK
        bigint ID_ARTICULO
        int ID_OPERACION FK
        decimal UNIDADES_HORA_STD
        int MINUTOS_PREPARACION_STD
        bit ACTIVO
    }

    PQ_PRD_MAQUINAS {
        int ID_MAQUINA PK
        nvarchar CODIGO_MAQUINA UK
        nvarchar NOMBRE
        bit ACTIVA
    }

    PQ_PRD_TIPOS_TAREA {
        int ID_TIPO_TAREA PK
        nvarchar CODIGO_TIPO_TAREA UK
        nvarchar NOMBRE
        bit ACTIVO
    }

    PQ_PRD_CONCEPTOS_TIEMPO {
        int ID_CONCEPTO_TIEMPO PK
        nvarchar CODIGO_CONCEPTO UK
        nvarchar NOMBRE
        bit ES_PRODUCTIVO
        bit ACTIVO
    }

    PQ_PRD_TURNOS {
        int ID_TURNO PK
        nvarchar CODIGO_TURNO UK
        nvarchar NOMBRE
        time HORA_INICIO
        time HORA_FIN
        bit ACTIVO
    }

    PQ_PRD_OPERACIONES ||--o{ PQ_PRD_ARTICULO_OPERACION_STD : "ID_OPERACION"
```

---

## 3. Módulo PLANIFICACIÓN (ASIGNACIÓN)

**Objetivo:** Planificar asignaciones por fecha/turno con items y operarios asignados.

**Relaciones:**
- 1 asignación → varios items
- 1 item → varios operarios asignados

```mermaid
erDiagram
    PQ_PRD_TURNOS {
        int ID_TURNO PK
        nvarchar CODIGO_TURNO UK
    }

    PQ_PRD_ASIGNACIONES {
        bigint ID_ASIGNACION PK
        date FECHA_ASIGNACION
        int ID_TURNO FK
        bigint ID_USUARIO_SUPERVISOR
        tinyint ESTADO
    }

    PQ_PRD_ORDENES_TRABAJO {
        bigint ID_ORDEN_TRABAJO PK
        nvarchar CODIGO_OT UK
    }

    PQ_PRD_ASIGNACIONES_ITEMS {
        bigint ID_ASIGNACION_ITEM PK
        bigint ID_ASIGNACION FK
        bigint ID_ORDEN_TRABAJO FK
        int ID_OPERACION FK
        int ID_TIPO_TAREA FK
        int ID_MAQUINA FK
        decimal UNIDADES_HORA_STD "snapshot"
        decimal UNIDADES_PLAN
        int MINUTOS_PLAN
    }

    PQ_PRD_ASIGNACIONES_ITEMS_OPERARIOS {
        bigint ID_ASIGITEM_OPERARIO PK
        bigint ID_ASIGNACION_ITEM FK
        bigint ID_OPERARIO
    }

    PQ_PRD_TURNOS ||--o{ PQ_PRD_ASIGNACIONES : "ID_TURNO"
    PQ_PRD_ASIGNACIONES ||--o{ PQ_PRD_ASIGNACIONES_ITEMS : "ID_ASIGNACION"
    PQ_PRD_ORDENES_TRABAJO ||--o{ PQ_PRD_ASIGNACIONES_ITEMS : "ID_ORDEN_TRABAJO"
    PQ_PRD_ASIGNACIONES_ITEMS ||--o{ PQ_PRD_ASIGNACIONES_ITEMS_OPERARIOS : "ID_ASIGNACION_ITEM"
```

---

## 4. Módulo EJECUCIÓN (PARTE REAL)

**Objetivo:** Registrar parte por operario y entradas de trabajo (tiempo, concepto, unidades).

**Relaciones:**
- 1 parte operario → varias entradas
- Entrada puede vincularse a item de asignación o ser libre

```mermaid
erDiagram
    PQ_PRD_PARTES_OPERARIO {
        bigint ID_PARTE_OPERARIO PK
        date FECHA_PARTE
        int ID_TURNO
        bigint ID_OPERARIO
        tinyint ESTADO "0=Abierto 1=Enviado 2=Revisado 3=Bloqueado"
    }

    PQ_PRD_PARTES_ENTRADAS {
        bigint ID_PARTE_ENTRADA PK
        bigint ID_PARTE_OPERARIO FK
        bigint ID_ASIGNACION_ITEM FK
        bigint ID_ORDEN_TRABAJO FK
        int ID_CONCEPTO_TIEMPO FK
        int MINUTOS
        decimal UNIDADES_HECHAS
        decimal UNIDADES_MERMA
        decimal UNIDADES_RETRABAJO
    }

    PQ_PRD_ASIGNACIONES_ITEMS {
        bigint ID_ASIGNACION_ITEM PK
    }

    PQ_PRD_ORDENES_TRABAJO {
        bigint ID_ORDEN_TRABAJO PK
    }

    PQ_PRD_CONCEPTOS_TIEMPO {
        int ID_CONCEPTO_TIEMPO PK
        bit ES_PRODUCTIVO
    }

    PQ_PRD_PARTES_OPERARIO ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_PARTE_OPERARIO"
    PQ_PRD_ASIGNACIONES_ITEMS ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_ASIGNACION_ITEM"
    PQ_PRD_ORDENES_TRABAJO ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_ORDEN_TRABAJO"
    PQ_PRD_CONCEPTOS_TIEMPO ||--o{ PQ_PRD_PARTES_ENTRADAS : "ID_CONCEPTO_TIEMPO"
```

---

## Resumen de módulos

| Módulo | Tablas | Estado relaciones |
|--------|--------|-------------------|
| **CATÁLOGOS** | PQ_PRD_ORDENES_TRABAJO, PQ_PRD_OPERACIONES, PQ_PRD_ARTICULO_OPERACION_STD, PQ_PRD_MAQUINAS, PQ_PRD_TIPOS_TAREA, PQ_PRD_CONCEPTOS_TIEMPO, PQ_PRD_TURNOS | Definidas |
| **PLANIFICACIÓN** | PQ_PRD_ASIGNACIONES, PQ_PRD_ASIGNACIONES_ITEMS, PQ_PRD_ASIGNACIONES_ITEMS_OPERARIOS | Cabecera-Detalle |
| **EJECUCIÓN** | PQ_PRD_PARTES_OPERARIO, PQ_PRD_PARTES_ENTRADAS | Cabecera-Detalle |
| **VISTAS** | PQ_PRD_VW_EFICIENCIA_POR_ENTRADA, PQ_PRD_VW_EFICIENCIA_DIARIA_OPERARIO | Analíticas (no persistidas) |
