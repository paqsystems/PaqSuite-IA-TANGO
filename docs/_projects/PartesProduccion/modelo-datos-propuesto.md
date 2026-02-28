# Documento 1 (v3) — Modelo de Datos Propuesto (Refactor Limpio) — Nombres en Español
Módulo: PQ_PRD (Gestión de Tareas + Eficiencia Operaria)
DB: Company DB (por tenant/empresa). SQL Server 2016+.

## 0. Principios de diseño del modelo
- Finalidad: medir eficiencia/productividad de operarios mediante:
  - Asignación planificada (estándares/objetivos)
  - Ejecución real (tiempos reales / unidades reales / improductivos)
- NO incluye: stock, consumos, depósitos, lotes, BOM, MRP, costeo contable integrado.
- Evitar redundancias:
  - El estándar y objetivos se “congelan” en el detalle de asignación (snapshot al publicar).
  - La ejecución no repite estándar salvo necesidad.
- Auditoría: CreatedAt/By, UpdatedAt/By, ReviewedAt/By y Estados.

---

## 1. Catálogos / Maestros

### 1.1. PQ_PRD_ORDENES_TRABAJO (OT)
Objetivo: OT propia del módulo, con referencias externas opcionales.

Campos:
- ID_ORDEN_TRABAJO (PK, bigint identity)
- CODIGO_OT (nvarchar(30), UNIQUE)                 // "OT-2026-000123"
- TIPO_REF_EXTERNA (nvarchar(20), null)           // "TANGO", "CZ", etc.
- ID_REF_EXTERNA (nvarchar(50), null)
- DESCRIPCION (nvarchar(200), null)
- ID_ARTICULO (bigint, null)                 // FK -> STA11.ID_STA11 (Tango)
- FECHA_INICIO_PLAN (date, null)
- FECHA_FIN_PLAN (date, null)
- ESTADO (tinyint not null)                       // 0=Borrador 1=Abierta 2=Cerrada 3=Anulada
- OBSERVACIONES (nvarchar(max), null)
- FECHA_ALTA (datetime2 not null)
- USUARIO_ALTA (bigint not null)
- FECHA_MODIF (datetime2 null)
- USUARIO_MODIF (bigint null)

Índices:
- UX_PQ_PRD_ORDENES_TRABAJO_CODIGO (CODIGO_OT)
- IX_PQ_PRD_ORDENES_TRABAJO_ESTADO (ESTADO)

---

### 1.2. PQ_PRD_OPERACIONES (Catálogo de operaciones)
Campos:
- ID_OPERACION (PK, int identity)
- CODIGO_OPERACION (nvarchar(20), UNIQUE)          // "CORT", "SOLD"
- NOMBRE (nvarchar(100) not null)
- ACTIVA (bit not null)

---

### 1.3. PQ_PRD_ARTICULO_OPERACION_STD (Estándar por Artículo+Operación)
Campos:
- ID_ARTICULO_OPERACION_STD (PK, bigint identity)
- ID_ARTICULO (bigint not null)              // FK -> STA11.ID_STA11 (Tango)
- ID_OPERACION (int not null FK -> PQ_PRD_OPERACIONES.ID_OPERACION)
- UNIDADES_HORA_STD (decimal(10,2) not null)       // piezas/hora
- MINUTOS_PREPARACION_STD (int, null)
- NOTAS (nvarchar(200), null)
- VIGENTE_DESDE (date, null)
- VIGENTE_HASTA (date, null)
- ACTIVO (bit not null)

Índices:
- IX_PQ_PRD_ARTOPSTD_ART_OP_ACT (ID_ARTICULO, ID_OPERACION, ACTIVO)

---

### 1.4. PQ_PRD_MAQUINAS
Campos:
- ID_MAQUINA (int identity PK)
- CODIGO_MAQUINA (nvarchar(20), UNIQUE)
- NOMBRE (nvarchar(100) not null)
- ACTIVA (bit not null)

---

### 1.5. PQ_PRD_TIPOS_TAREA
Campos:
- ID_TIPO_TAREA (int identity PK)
- CODIGO_TIPO_TAREA (nvarchar(20), UNIQUE)
- NOMBRE (nvarchar(100) not null)
- ACTIVO (bit not null)

---

### 1.6. PQ_PRD_CONCEPTOS_TIEMPO (Productivo / No productivo)
Campos:
- ID_CONCEPTO_TIEMPO (int identity PK)
- CODIGO_CONCEPTO (nvarchar(20), UNIQUE)
- NOMBRE (nvarchar(120) not null)
- ES_PRODUCTIVO (bit not null)
- ACTIVO (bit not null)

---

### 1.7. PQ_PRD_TURNOS (Opcional recomendado)
Campos:
- ID_TURNO (int identity PK)
- CODIGO_TURNO (nvarchar(20), UNIQUE)              // "Mañana", "Tarde", "Noche"
- NOMBRE (nvarchar(50) not null)
- HORA_INICIO (time(0), null)
- HORA_FIN (time(0), null)
- ACTIVO (bit not null)

---

## 2. Planificación (Asignación)

### 2.1. PQ_PRD_ASIGNACIONES (Cabecera)
Campos:
- ID_ASIGNACION (PK, bigint identity)
- FECHA_ASIGNACION (date not null)
- ID_TURNO (int null FK -> PQ_PRD_TURNOS.ID_TURNO)
- ID_USUARIO_SUPERVISOR (bigint not null)          // referencia a seguridad (usuario)
- OBSERVACIONES (nvarchar(max), null)
- ESTADO (tinyint not null)                        // 0=Borrador 1=Publicada 2=Cerrada 3=Anulada
- FECHA_PUBLICACION (datetime2, null)
- FECHA_CIERRE (datetime2, null)
- FECHA_ALTA (datetime2 not null)
- USUARIO_ALTA (bigint not null)
- FECHA_MODIF (datetime2 null)
- USUARIO_MODIF (bigint null)

Índices:
- IX_PQ_PRD_ASIGNACIONES_FECHA (FECHA_ASIGNACION, ID_TURNO, ESTADO)

---

### 2.2. PQ_PRD_ASIGNACIONES_ITEMS (Detalle planificado)
Campos:
- ID_ASIGNACION_ITEM (PK, bigint identity)
- ID_ASIGNACION (FK -> PQ_PRD_ASIGNACIONES.ID_ASIGNACION)

Referencias:
- ID_ORDEN_TRABAJO (bigint null FK -> PQ_PRD_ORDENES_TRABAJO.ID_ORDEN_TRABAJO)
- ID_ARTICULO (bigint, null)                 // FK -> STA11.ID_STA11 (Tango)
- ID_OPERACION (int, null FK -> PQ_PRD_OPERACIONES.ID_OPERACION)
- ID_TIPO_TAREA (int not null FK -> PQ_PRD_TIPOS_TAREA.ID_TIPO_TAREA)
- ID_MAQUINA (int, null FK -> PQ_PRD_MAQUINAS.ID_MAQUINA)

Snapshot (congelado al publicar):
- UNIDADES_HORA_STD (decimal(10,2), null)
- UNIDADES_PLAN (decimal(12,2), null)              // objetivo de unidades
- MINUTOS_PLAN (int, null)                         // duración teórica
- NOTAS_PLAN (nvarchar(200), null)

Control:
- PRIORIDAD (tinyint, null)
- ACTIVO (bit not null default 1)

Auditoría:
- FECHA_ALTA (datetime2 not null)
- USUARIO_ALTA (bigint not null)
- FECHA_MODIF (datetime2 null)
- USUARIO_MODIF (bigint null)

Índices:
- IX_PQ_PRD_ASIGITEM_ASIG (ID_ASIGNACION)
- IX_PQ_PRD_ASIGITEM_OT (ID_ORDEN_TRABAJO)

---

### 2.3. PQ_PRD_ASIGNACIONES_ITEMS_OPERARIOS (Operarios asignados)
Campos:
- ID_ASIGITEM_OPERARIO (PK, bigint identity)
- ID_ASIGNACION_ITEM (FK -> PQ_PRD_ASIGNACIONES_ITEMS.ID_ASIGNACION_ITEM)
- ID_OPERARIO (bigint not null)              // FK -> PQ_SUELD_LEGAJOS.ID
- ROL_PLAN (nvarchar(50), null)
- FECHA_ALTA (datetime2 not null)
- USUARIO_ALTA (bigint not null)

Índices:
- UX_PQ_PRD_ASIGITEM_OPERARIO_UNQ (ID_ASIGNACION_ITEM, ID_OPERARIO)

---

## 3. Ejecución (Parte real)

### 3.1. PQ_PRD_PARTES_OPERARIO (Parte por operario por fecha/turno)
Campos:
- ID_PARTE_OPERARIO (PK, bigint identity)
- FECHA_PARTE (date not null)
- ID_TURNO (int null)
- ID_OPERARIO (bigint not null)              // FK -> PQ_SUELD_LEGAJOS.ID
- FECHA_APERTURA (datetime2, null)
- FECHA_CIERRE (datetime2, null)
- ESTADO (tinyint not null)                        // 0=Abierto 1=Enviado 2=Revisado 3=Bloqueado
- OBSERVACIONES (nvarchar(max), null)

Revisión:
- FECHA_REVISION (datetime2, null)
- ID_USUARIO_REVISION (bigint, null)

Auditoría:
- FECHA_ALTA (datetime2 not null)
- USUARIO_ALTA (bigint not null)
- FECHA_MODIF (datetime2 null)
- USUARIO_MODIF (bigint null)

Índices:
- UX_PQ_PRD_PARTE_UNQ (FECHA_PARTE, ID_TURNO, ID_OPERARIO)

---

### 3.2. PQ_PRD_PARTES_ENTRADAS (Entradas de trabajo)
Campos:
- ID_PARTE_ENTRADA (PK, bigint identity)
- ID_PARTE_OPERARIO (FK -> PQ_PRD_PARTES_OPERARIO.ID_PARTE_OPERARIO)

Referencias:
- ID_ASIGNACION_ITEM (bigint, null FK -> PQ_PRD_ASIGNACIONES_ITEMS.ID_ASIGNACION_ITEM)
- ID_ORDEN_TRABAJO (bigint, null FK -> PQ_PRD_ORDENES_TRABAJO.ID_ORDEN_TRABAJO)
- ID_ARTICULO (bigint, null)                 // FK -> STA11.ID_STA11 (Tango)
- ID_OPERACION (int, null)
- ID_MAQUINA (int, null)
- ID_TIPO_TAREA (int, null)

Tiempo:
- FECHA_HORA_DESDE (datetime2(0), null)
- FECHA_HORA_HASTA (datetime2(0), null)
- MINUTOS (int, null)
- ID_CONCEPTO_TIEMPO (int not null FK -> PQ_PRD_CONCEPTOS_TIEMPO.ID_CONCEPTO_TIEMPO)

Unidades (solo si concepto productivo):
- UNIDADES_HECHAS (decimal(12,2), null)
- UNIDADES_MERMA (decimal(12,2), null)
- UNIDADES_RETRABAJO (decimal(12,2), null)

Texto:
- NOTAS (nvarchar(200), null)

Revisión por línea (opcional):
- FECHA_REVISION (datetime2, null)
- ID_USUARIO_REVISION (bigint, null)
- NOTAS_REVISION (nvarchar(200), null)

Auditoría:
- FECHA_ALTA (datetime2 not null)
- USUARIO_ALTA (bigint not null)
- FECHA_MODIF (datetime2 null)
- USUARIO_MODIF (bigint null)

Índices:
- IX_PQ_PRD_ENTRADAS_PARTE (ID_PARTE_OPERARIO)
- IX_PQ_PRD_ENTRADAS_ASIGITEM (ID_ASIGNACION_ITEM)
- IX_PQ_PRD_ENTRADAS_CONCEPTO (ID_CONCEPTO_TIEMPO)

---

## 4. Constraints recomendados
- Si FECHA_HORA_DESDE y FECHA_HORA_HASTA no son null => FECHA_HORA_HASTA > FECHA_HORA_DESDE.
- MINUTOS:
  - si null y hay FECHA_HORA_DESDE/FECHA_HORA_HASTA => se deriva.
  - si viene informada con timestamps => backend recalcula (fuente de verdad).
- Si PQ_PRD_CONCEPTOS_TIEMPO.ES_PRODUCTIVO = 0 => UNIDADES_* deben ser null o 0.
- Un parte no puede pasar a Enviado si tiene entradas inválidas.

Fin Documento.