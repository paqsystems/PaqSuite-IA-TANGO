# BASE DE DATOS : PQ_PRD (Partes Producción)

> **Diagramas Mermaid:** Ver `md-partes-produccion-diagramas.md` para diagramas ER por módulo (CATÁLOGOS, PLANIFICACIÓN, EJECUCIÓN).
>
> **Módulo:** PQ_PRD – Gestión de Tareas + Eficiencia Operaria  
> **DB:** Company DB (por tenant/empresa). SQL Server 2016+.
>
> **Origen:** `docs/_projects/PartesProduccion/modelo-datos-propuesto.md`

---

## Política de tablas Tango

Los proyectos con Tango se gestionan **por separado** de los proyectos propios. Reglas obligatorias:

- **a)** El diseño de tablas Tango se proporciona en archivos separados (ver `docs/modelo-datos/tango/`).
- **b)** Las tablas Tango **no pueden ser alteradas** bajo ningún concepto por el equipo; excepción solo con especificación estricta del responsable.

Ver `.cursor/rules/25-tablas-tango-politica.md` para el detalle completo.

---

## Principios de diseño del modelo

- **Finalidad:** medir eficiencia/productividad de operarios mediante:
  - Asignación planificada (estándares/objetivos)
  - Ejecución real (tiempos reales / unidades reales / improductivos)
- **NO incluye:** stock, consumos, depósitos, lotes, BOM, MRP, costeo contable integrado.
- **Evitar redundancias:** El estándar y objetivos se "congelan" en el detalle de asignación (snapshot al publicar).
- **Auditoría:** CreatedAt/By, UpdatedAt/By, ReviewedAt/By y Estados.

---

## 1. Catálogos / Maestros

### 1.1. PQ_PRD_ORDENES_TRABAJO

CREATE TABLE [dbo].[PQ_PRD_ORDENES_TRABAJO](
	[ID_ORDEN_TRABAJO] [bigint] IDENTITY(1,1) NOT NULL,
	[CODIGO_OT] [nvarchar](30) NOT NULL,					-- "OT-2026-000123"
	[TIPO_REF_EXTERNA] [nvarchar](20) NULL,				-- "TANGO", "CZ", etc.
	[ID_REF_EXTERNA] [nvarchar](50) NULL,
	[DESCRIPCION] [nvarchar](200) NULL,
	[ID_ARTICULO] [bigint] NULL,						-- FK -> STA11.ID_STA11 (Tango)
	[FECHA_INICIO_PLAN] [date] NULL,
	[FECHA_FIN_PLAN] [date] NULL,
	[ESTADO] [tinyint] NOT NULL,							-- 0=Borrador 1=Abierta 2=Cerrada 3=Anulada
	[OBSERVACIONES] [nvarchar](max) NULL,
	[FECHA_ALTA] [datetime2](7) NOT NULL,
	[USUARIO_ALTA] [bigint] NOT NULL,
	[FECHA_MODIF] [datetime2](7) NULL,
	[USUARIO_MODIF] [bigint] NULL,
 CONSTRAINT [PK_PQ_PRD_ORDENES_TRABAJO] PRIMARY KEY CLUSTERED ([ID_ORDEN_TRABAJO] ASC),
 CONSTRAINT [UX_PQ_PRD_ORDENES_TRABAJO_CODIGO] UNIQUE ([CODIGO_OT])
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_PQ_PRD_ORDENES_TRABAJO_ESTADO] ON [dbo].[PQ_PRD_ORDENES_TRABAJO]([ESTADO])
GO

---

### 1.2. PQ_PRD_OPERACIONES

CREATE TABLE [dbo].[PQ_PRD_OPERACIONES](
	[ID_OPERACION] [int] IDENTITY(1,1) NOT NULL,
	[CODIGO_OPERACION] [nvarchar](20) NOT NULL,			-- "CORT", "SOLD"
	[NOMBRE] [nvarchar](100) NOT NULL,
	[ACTIVA] [bit] NOT NULL,
 CONSTRAINT [PK_PQ_PRD_OPERACIONES] PRIMARY KEY CLUSTERED ([ID_OPERACION] ASC),
 CONSTRAINT [UX_PQ_PRD_OPERACIONES_CODIGO] UNIQUE ([CODIGO_OPERACION])
) ON [PRIMARY]
GO

---

### 1.3. PQ_PRD_ARTICULO_OPERACION_STD

CREATE TABLE [dbo].[PQ_PRD_ARTICULO_OPERACION_STD](
	[ID_ARTICULO_OPERACION_STD] [bigint] IDENTITY(1,1) NOT NULL,
	[ID_ARTICULO] [bigint] NOT NULL,					-- FK -> STA11.ID_STA11 (Tango)
	[ID_OPERACION] [int] NOT NULL,						-- FK -> PQ_PRD_OPERACIONES.ID_OPERACION
	[UNIDADES_HORA_STD] [decimal](10,2) NOT NULL,			-- piezas/hora
	[MINUTOS_PREPARACION_STD] [int] NULL,
	[NOTAS] [nvarchar](200) NULL,
	[VIGENTE_DESDE] [date] NULL,
	[VIGENTE_HASTA] [date] NULL,
	[ACTIVO] [bit] NOT NULL,
 CONSTRAINT [PK_PQ_PRD_ARTICULO_OPERACION_STD] PRIMARY KEY CLUSTERED ([ID_ARTICULO_OPERACION_STD] ASC)
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_PQ_PRD_ARTOPSTD_ART_OP_ACT] ON [dbo].[PQ_PRD_ARTICULO_OPERACION_STD]([ID_ARTICULO], [ID_OPERACION], [ACTIVO])
GO

---

### 1.4. PQ_PRD_MAQUINAS

CREATE TABLE [dbo].[PQ_PRD_MAQUINAS](
	[ID_MAQUINA] [int] IDENTITY(1,1) NOT NULL,
	[CODIGO_MAQUINA] [nvarchar](20) NOT NULL,
	[NOMBRE] [nvarchar](100) NOT NULL,
	[ACTIVA] [bit] NOT NULL,
 CONSTRAINT [PK_PQ_PRD_MAQUINAS] PRIMARY KEY CLUSTERED ([ID_MAQUINA] ASC),
 CONSTRAINT [UX_PQ_PRD_MAQUINAS_CODIGO] UNIQUE ([CODIGO_MAQUINA])
) ON [PRIMARY]
GO

---

### 1.5. PQ_PRD_TIPOS_TAREA

CREATE TABLE [dbo].[PQ_PRD_TIPOS_TAREA](
	[ID_TIPO_TAREA] [int] IDENTITY(1,1) NOT NULL,
	[CODIGO_TIPO_TAREA] [nvarchar](20) NOT NULL,
	[NOMBRE] [nvarchar](100) NOT NULL,
	[ACTIVO] [bit] NOT NULL,
 CONSTRAINT [PK_PQ_PRD_TIPOS_TAREA] PRIMARY KEY CLUSTERED ([ID_TIPO_TAREA] ASC),
 CONSTRAINT [UX_PQ_PRD_TIPOS_TAREA_CODIGO] UNIQUE ([CODIGO_TIPO_TAREA])
) ON [PRIMARY]
GO

---

### 1.6. PQ_PRD_CONCEPTOS_TIEMPO

CREATE TABLE [dbo].[PQ_PRD_CONCEPTOS_TIEMPO](
	[ID_CONCEPTO_TIEMPO] [int] IDENTITY(1,1) NOT NULL,
	[CODIGO_CONCEPTO] [nvarchar](20) NOT NULL,
	[NOMBRE] [nvarchar](120) NOT NULL,
	[ES_PRODUCTIVO] [bit] NOT NULL,
	[ACTIVO] [bit] NOT NULL,
 CONSTRAINT [PK_PQ_PRD_CONCEPTOS_TIEMPO] PRIMARY KEY CLUSTERED ([ID_CONCEPTO_TIEMPO] ASC),
 CONSTRAINT [UX_PQ_PRD_CONCEPTOS_TIEMPO_CODIGO] UNIQUE ([CODIGO_CONCEPTO])
) ON [PRIMARY]
GO

---

### 1.7. PQ_PRD_TURNOS

CREATE TABLE [dbo].[PQ_PRD_TURNOS](
	[ID_TURNO] [int] IDENTITY(1,1) NOT NULL,
	[CODIGO_TURNO] [nvarchar](20) NOT NULL,				-- "Mañana", "Tarde", "Noche"
	[NOMBRE] [nvarchar](50) NOT NULL,
	[HORA_INICIO] [time](0) NULL,
	[HORA_FIN] [time](0) NULL,
	[ACTIVO] [bit] NOT NULL,
 CONSTRAINT [PK_PQ_PRD_TURNOS] PRIMARY KEY CLUSTERED ([ID_TURNO] ASC),
 CONSTRAINT [UX_PQ_PRD_TURNOS_CODIGO] UNIQUE ([CODIGO_TURNO])
) ON [PRIMARY]
GO

---

## 2. Planificación (Asignación)

### 2.1. PQ_PRD_ASIGNACIONES

CREATE TABLE [dbo].[PQ_PRD_ASIGNACIONES](
	[ID_ASIGNACION] [bigint] IDENTITY(1,1) NOT NULL,
	[FECHA_ASIGNACION] [date] NOT NULL,
	[ID_TURNO] [int] NULL,								-- FK -> PQ_PRD_TURNOS.ID_TURNO
	[ID_USUARIO_SUPERVISOR] [bigint] NOT NULL,			-- referencia a seguridad (usuario)
	[OBSERVACIONES] [nvarchar](max) NULL,
	[ESTADO] [tinyint] NOT NULL,						-- 0=Borrador 1=Publicada 2=Cerrada 3=Anulada
	[FECHA_PUBLICACION] [datetime2](7) NULL,
	[FECHA_CIERRE] [datetime2](7) NULL,
	[FECHA_ALTA] [datetime2](7) NOT NULL,
	[USUARIO_ALTA] [bigint] NOT NULL,
	[FECHA_MODIF] [datetime2](7) NULL,
	[USUARIO_MODIF] [bigint] NULL,
 CONSTRAINT [PK_PQ_PRD_ASIGNACIONES] PRIMARY KEY CLUSTERED ([ID_ASIGNACION] ASC)
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_PQ_PRD_ASIGNACIONES_FECHA] ON [dbo].[PQ_PRD_ASIGNACIONES]([FECHA_ASIGNACION], [ID_TURNO], [ESTADO])
GO

---

### 2.2. PQ_PRD_ASIGNACIONES_ITEMS

CREATE TABLE [dbo].[PQ_PRD_ASIGNACIONES_ITEMS](
	[ID_ASIGNACION_ITEM] [bigint] IDENTITY(1,1) NOT NULL,
	[ID_ASIGNACION] [bigint] NOT NULL,					-- FK -> PQ_PRD_ASIGNACIONES.ID_ASIGNACION
	[ID_ORDEN_TRABAJO] [bigint] NULL,					-- FK -> PQ_PRD_ORDENES_TRABAJO.ID_ORDEN_TRABAJO
	[ID_ARTICULO] [bigint] NULL,						-- FK -> STA11.ID_STA11 (Tango)
	[ID_OPERACION] [int] NULL,							-- FK -> PQ_PRD_OPERACIONES.ID_OPERACION
	[ID_TIPO_TAREA] [int] NOT NULL,						-- FK -> PQ_PRD_TIPOS_TAREA.ID_TIPO_TAREA
	[ID_MAQUINA] [int] NULL,							-- FK -> PQ_PRD_MAQUINAS.ID_MAQUINA
	[UNIDADES_HORA_STD] [decimal](10,2) NULL,			-- snapshot al publicar
	[UNIDADES_PLAN] [decimal](12,2) NULL,				-- objetivo de unidades
	[MINUTOS_PLAN] [int] NULL,							-- duración teórica
	[NOTAS_PLAN] [nvarchar](200) NULL,
	[PRIORIDAD] [tinyint] NULL,
	[ACTIVO] [bit] NOT NULL DEFAULT 1,
	[FECHA_ALTA] [datetime2](7) NOT NULL,
	[USUARIO_ALTA] [bigint] NOT NULL,
	[FECHA_MODIF] [datetime2](7) NULL,
	[USUARIO_MODIF] [bigint] NULL,
 CONSTRAINT [PK_PQ_PRD_ASIGNACIONES_ITEMS] PRIMARY KEY CLUSTERED ([ID_ASIGNACION_ITEM] ASC)
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_PQ_PRD_ASIGITEM_ASIG] ON [dbo].[PQ_PRD_ASIGNACIONES_ITEMS]([ID_ASIGNACION])
GO
CREATE NONCLUSTERED INDEX [IX_PQ_PRD_ASIGITEM_OT] ON [dbo].[PQ_PRD_ASIGNACIONES_ITEMS]([ID_ORDEN_TRABAJO])
GO

---

### 2.3. PQ_PRD_ASIGNACIONES_ITEMS_OPERARIOS

CREATE TABLE [dbo].[PQ_PRD_ASIGNACIONES_ITEMS_OPERARIOS](
	[ID_ASIGITEM_OPERARIO] [bigint] IDENTITY(1,1) NOT NULL,
	[ID_ASIGNACION_ITEM] [bigint] NOT NULL,				-- FK -> PQ_PRD_ASIGNACIONES_ITEMS.ID_ASIGNACION_ITEM
	[ID_OPERARIO] [bigint] NOT NULL,					-- FK -> PQ_SUELD_LEGAJOS.ID
	[ROL_PLAN] [nvarchar](50) NULL,
	[FECHA_ALTA] [datetime2](7) NOT NULL,
	[USUARIO_ALTA] [bigint] NOT NULL,
 CONSTRAINT [PK_PQ_PRD_ASIGNACIONES_ITEMS_OPERARIOS] PRIMARY KEY CLUSTERED ([ID_ASIGITEM_OPERARIO] ASC),
 CONSTRAINT [UX_PQ_PRD_ASIGITEM_OPERARIO_UNQ] UNIQUE ([ID_ASIGNACION_ITEM], [ID_OPERARIO])
) ON [PRIMARY]
GO

---

## 3. Ejecución (Parte real)

### 3.1. PQ_PRD_PARTES_OPERARIO

CREATE TABLE [dbo].[PQ_PRD_PARTES_OPERARIO](
	[ID_PARTE_OPERARIO] [bigint] IDENTITY(1,1) NOT NULL,
	[FECHA_PARTE] [date] NOT NULL,
	[ID_TURNO] [int] NULL,
	[ID_OPERARIO] [bigint] NOT NULL,					-- FK -> PQ_SUELD_LEGAJOS.ID
	[FECHA_APERTURA] [datetime2](7) NULL,
	[FECHA_CIERRE] [datetime2](7) NULL,
	[ESTADO] [tinyint] NOT NULL,						-- 0=Abierto 1=Enviado 2=Revisado 3=Bloqueado
	[OBSERVACIONES] [nvarchar](max) NULL,
	[FECHA_REVISION] [datetime2](7) NULL,
	[ID_USUARIO_REVISION] [bigint] NULL,
	[FECHA_ALTA] [datetime2](7) NOT NULL,
	[USUARIO_ALTA] [bigint] NOT NULL,
	[FECHA_MODIF] [datetime2](7) NULL,
	[USUARIO_MODIF] [bigint] NULL,
 CONSTRAINT [PK_PQ_PRD_PARTES_OPERARIO] PRIMARY KEY CLUSTERED ([ID_PARTE_OPERARIO] ASC),
 CONSTRAINT [UX_PQ_PRD_PARTE_UNQ] UNIQUE ([FECHA_PARTE], [ID_TURNO], [ID_OPERARIO])
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

---

### 3.2. PQ_PRD_PARTES_ENTRADAS

CREATE TABLE [dbo].[PQ_PRD_PARTES_ENTRADAS](
	[ID_PARTE_ENTRADA] [bigint] IDENTITY(1,1) NOT NULL,
	[ID_PARTE_OPERARIO] [bigint] NOT NULL,				-- FK -> PQ_PRD_PARTES_OPERARIO.ID_PARTE_OPERARIO
	[ID_ASIGNACION_ITEM] [bigint] NULL,					-- FK -> PQ_PRD_ASIGNACIONES_ITEMS.ID_ASIGNACION_ITEM
	[ID_ORDEN_TRABAJO] [bigint] NULL,					-- FK -> PQ_PRD_ORDENES_TRABAJO.ID_ORDEN_TRABAJO
	[ID_ARTICULO] [bigint] NULL,						-- FK -> STA11.ID_STA11 (Tango)
	[ID_OPERACION] [int] NULL,
	[ID_MAQUINA] [int] NULL,
	[ID_TIPO_TAREA] [int] NULL,
	[FECHA_HORA_DESDE] [datetime2](0) NULL,
	[FECHA_HORA_HASTA] [datetime2](0) NULL,
	[MINUTOS] [int] NULL,
	[ID_CONCEPTO_TIEMPO] [int] NOT NULL,				-- FK -> PQ_PRD_CONCEPTOS_TIEMPO.ID_CONCEPTO_TIEMPO
	[UNIDADES_HECHAS] [decimal](12,2) NULL,
	[UNIDADES_MERMA] [decimal](12,2) NULL,
	[UNIDADES_RETRABAJO] [decimal](12,2) NULL,
	[NOTAS] [nvarchar](200) NULL,
	[FECHA_REVISION] [datetime2](7) NULL,
	[ID_USUARIO_REVISION] [bigint] NULL,
	[NOTAS_REVISION] [nvarchar](200) NULL,
	[FECHA_ALTA] [datetime2](7) NOT NULL,
	[USUARIO_ALTA] [bigint] NOT NULL,
	[FECHA_MODIF] [datetime2](7) NULL,
	[USUARIO_MODIF] [bigint] NULL,
 CONSTRAINT [PK_PQ_PRD_PARTES_ENTRADAS] PRIMARY KEY CLUSTERED ([ID_PARTE_ENTRADA] ASC)
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_PQ_PRD_ENTRADAS_PARTE] ON [dbo].[PQ_PRD_PARTES_ENTRADAS]([ID_PARTE_OPERARIO])
GO
CREATE NONCLUSTERED INDEX [IX_PQ_PRD_ENTRADAS_ASIGITEM] ON [dbo].[PQ_PRD_PARTES_ENTRADAS]([ID_ASIGNACION_ITEM])
GO
CREATE NONCLUSTERED INDEX [IX_PQ_PRD_ENTRADAS_CONCEPTO] ON [dbo].[PQ_PRD_PARTES_ENTRADAS]([ID_CONCEPTO_TIEMPO])
GO

---

## 4. Vistas analíticas (no persistir KPI)

### 4.1. PQ_PRD_VW_EFICIENCIA_POR_ENTRADA
- Une PQ_PRD_PARTES_ENTRADAS + PQ_PRD_CONCEPTOS_TIEMPO + PQ_PRD_ASIGNACIONES_ITEMS (snapshot) + catálogos.
- Calcula MINUTOS_PRODUCTIVOS / MINUTOS_IMPRODUCTIVOS, UNIDADES_TEORICAS, EFICIENCIA_PCT.

### 4.2. PQ_PRD_VW_EFICIENCIA_DIARIA_OPERARIO
- Agrupa por fecha/turno/operario y calcula KPI diarios.

---

# Definiciones de cada tema

---

## CATÁLOGOS / MAESTROS

### 1) Objetivo
Definir órdenes de trabajo, operaciones, estándares artículo-operación, máquinas, tipos de tarea, conceptos de tiempo (productivo/no productivo) y turnos.

### 2) Relaciones
- PQ_PRD_ORDENES_TRABAJO: OT propia del módulo con referencias externas opcionales.
- PQ_PRD_ARTICULO_OPERACION_STD: estándar por artículo+operación (FK a PQ_PRD_OPERACIONES).
- PQ_PRD_TURNOS: opcional, para horarios de turno.

### 3) Reglas de negocio
- CODIGO_OT único en formato "OT-2026-000123".
- ESTADO en OT: 0=Borrador, 1=Abierta, 2=Cerrada, 3=Anulada.
- PQ_PRD_CONCEPTOS_TIEMPO.ES_PRODUCTIVO: si 0, no se registran unidades en entradas.
- Estándar: UNIDADES_HORA_STD (piezas/hora), MINUTOS_PREPARACION_STD.

---

## PLANIFICACIÓN (ASIGNACIÓN)

### 1) Objetivo
Planificar asignaciones por fecha/turno con items (OT, artículo, operación, tipo tarea, máquina) y snapshot de estándar al publicar.

### 2) Relaciones
- 1 asignación → varios items de asignación.
- 1 item de asignación → varios operarios asignados.

### 3) Reglas de negocio
- ESTADO en asignación: 0=Borrador, 1=Publicada, 2=Cerrada, 3=Anulada.
- Snapshot: UNIDADES_HORA_STD, UNIDADES_PLAN, MINUTOS_PLAN se congelan al publicar.
- ID_USUARIO_SUPERVISOR: referencia a usuario de seguridad (Dictionary DB).

---

## EJECUCIÓN (PARTE REAL)

### 1) Objetivo
Registrar el parte por operario por fecha/turno y las entradas de trabajo (tiempo, concepto, unidades).

### 2) Relaciones
- 1 parte operario → varias entradas.
- Entrada puede vincularse a ID_ASIGNACION_ITEM (planificado) o ser libre.

### 3) Reglas de negocio
- ESTADO en parte: 0=Abierto, 1=Enviado, 2=Revisado, 3=Bloqueado.
- Si PQ_PRD_CONCEPTOS_TIEMPO.ES_PRODUCTIVO = 0 ⇒ UNIDADES_* deben ser null o 0.
- Un parte no puede pasar a Enviado si tiene entradas inválidas.
- FECHA_HORA_HASTA > FECHA_HORA_DESDE cuando ambos no son null.
- MINUTOS: si null y hay FECHA_HORA_DESDE/FECHA_HORA_HASTA ⇒ se deriva; si viene informada con timestamps ⇒ backend recalcula (fuente de verdad).

---

## Definiciones acordadas

| Tema | Definición |
|------|------------|
| **ID_ARTICULO** | `bigint`, FK a `STA11.ID_STA11` (maestro de artículos Tango). La app consume artículos vía vista `pq_vwarticulos`. |
| **ID_OPERARIO** | `bigint`, FK a `PQ_SUELD_LEGAJOS.ID` (tabla propia de legajos). La app consume operarios vía vista `pq_vwoperarios`. |

---

## Constraints recomendados

- Si FECHA_HORA_DESDE y FECHA_HORA_HASTA no son null ⇒ FECHA_HORA_HASTA > FECHA_HORA_DESDE.
- Si PQ_PRD_CONCEPTOS_TIEMPO.ES_PRODUCTIVO = 0 ⇒ UNIDADES_HECHAS, UNIDADES_MERMA, UNIDADES_RETRABAJO deben ser null o 0.
- Un parte no puede pasar a Enviado si tiene entradas inválidas.

