# BASE DE DATOS : PQ_SUELD (Sueldos / Legajos)

> **Diagramas Mermaid:** Ver `md-sueldos-diagramas.md` para diagramas ER.
>
> **Módulo:** PQ_SUELD – Catálogo de legajos de la empresa. se usará en diferentes módulos 
> **DB:** Company DB (por tenant/empresa). SQL Server 2016+.
>
> **Origen:** Definición inicial en este archivo.

---

## Política de tablas Tango

Los proyectos con Tango se gestionan **por separado** de los proyectos propios. Reglas obligatorias:

- **a)** El diseño de tablas Tango se proporciona en archivos separados (ver `docs/modelo-datos/tango/`).
- **b)** Las tablas Tango **no pueden ser alteradas** bajo ningún concepto por el equipo; excepción solo con especificación estricta del responsable.

Ver `.cursor/rules/25-tablas-tango-politica.md` para el detalle completo.

---

## Principios de diseño del modelo

- **Finalidad:** Catálogo de tareas (PQ_SUELD_TAREAS) y legajos/operarios (PQ_SUELD_LEGAJOS) para uso en módulos como Partes Producción.
- **PQ_SUELD_LEGAJOS** es la tabla de referencia para `ID_OPERARIO` en PQ_PRD (ver `md-partes-produccion.md`).
- **Relación con Dictionary DB:** ID_USUARIO en PQ_SUELD_LEGAJOS es FK lógica a `users.id` (tabla del Dictionary DB); no se implementa FK física entre bases.

---

## 1. Catálogos / Maestros

### 1.1. PQ_SUELD_TAREAS

CREATE TABLE [dbo].[PQ_SUELD_TAREAS](
	[ID] [bigint] IDENTITY(1,1) NOT NULL,
	[CODIGO] [nvarchar](30) NOT NULL,
	[DESCRIPCION] [nvarchar](200) NULL,
 CONSTRAINT [PK_PQ_SUELD_TAREAS] PRIMARY KEY CLUSTERED ([ID] ASC),
 CONSTRAINT [UX_PQ_SUELD_TAREAS_CODIGO] UNIQUE ([CODIGO])
) ON [PRIMARY]
GO

---

### 1.2. PQ_SUELD_LEGAJOS

CREATE TABLE [dbo].[PQ_SUELD_LEGAJOS](
	[ID] [bigint] IDENTITY(1,1) NOT NULL,
	[NRO_LEGAJO] [int] NOT NULL,
	[APELLIDO] [nvarchar](100) NULL,
	[NOMBRE] [nvarchar](100) NULL,
	[CUIL] [nvarchar](15) NULL,							-- no obligatorio por el momento
	[ID_USUARIO] [bigint] NULL,							-- FK lógica -> users.id (Dictionary DB)
	[ID_SUELD_TAREA] [bigint] NOT NULL,					-- FK -> PQ_SUELD_TAREAS.ID
 CONSTRAINT [PK_PQ_SUELD_LEGAJOS] PRIMARY KEY CLUSTERED ([ID] ASC)
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_PQ_SUELD_LEGAJOS_NRO_LEGAJO] ON [dbo].[PQ_SUELD_LEGAJOS]([NRO_LEGAJO])
GO
CREATE NONCLUSTERED INDEX [IX_PQ_SUELD_LEGAJOS_ID_USUARIO] ON [dbo].[PQ_SUELD_LEGAJOS]([ID_USUARIO])
GO
CREATE NONCLUSTERED INDEX [IX_PQ_SUELD_LEGAJOS_ID_SUELD_TAREA] ON [dbo].[PQ_SUELD_LEGAJOS]([ID_SUELD_TAREA])
GO

ALTER TABLE [dbo].[PQ_SUELD_LEGAJOS] WITH CHECK ADD CONSTRAINT [FK_PQ_SUELD_LEGAJOS_TAREAS] FOREIGN KEY([ID_SUELD_TAREA])
REFERENCES [dbo].[PQ_SUELD_TAREAS] ([ID])
GO

---

# Definiciones de cada tema

---

## CATÁLOGOS / MAESTROS

### 1) Objetivo
Definir tareas (PQ_SUELD_TAREAS) y legajos/operarios (PQ_SUELD_LEGAJOS) para uso en módulos operativos.

### 2) Relaciones
- PQ_SUELD_TAREAS: catálogo de tipos de tarea.
- PQ_SUELD_LEGAJOS: legajos con FK a PQ_SUELD_TAREAS; ID_USUARIO opcional (vinculación a usuario del Dictionary DB).

### 3) Reglas de negocio
- CODIGO único en PQ_SUELD_TAREAS.
- ID_SUELD_TAREA obligatorio en PQ_SUELD_LEGAJOS.
- ID_USUARIO nullable: permite legajos sin usuario asociado.

---

## Definiciones acordadas

| Tema | Definición |
|------|------------|
| **ID_OPERARIO** | `bigint`, FK a `PQ_SUELD_LEGAJOS.ID`. Referenciado desde PQ_PRD (Partes Producción) y otros módulos. |
| **ID_USUARIO** | `bigint`, FK lógica a `users.id` (Dictionary DB). Vincula legajo con usuario del sistema. |

---

## Constraints recomendados

- PQ_SUELD_LEGAJOS.ID_SUELD_TAREA debe existir en PQ_SUELD_TAREAS.
- Si se implementa validación cruzada con Dictionary DB, ID_USUARIO debe existir en users.id cuando no es null.
