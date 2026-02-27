# Documento 2 (v2) — Contexto Funcional y Conceptual (para poner en tema a la IDE)
Este documento NO asume conocimiento previo del sistema existente.

## 1. Qué problema resuelve este módulo
El módulo registra, por operario y por día/turno, qué tareas realizó y cuánto tiempo dedicó a:
- Actividades productivas (con unidades realizadas)
- Actividades no productivas (sin unidades, con un concepto)

Con esa información se calculan indicadores de eficiencia y productividad.

## 2. Qué NO resuelve (limitante explícita)
- No gestiona stock ni movimientos de depósito.
- No consume materiales ni registra BOM.
- No planifica MRP.
- No genera asientos contables.

## 3. Flujo operativo
1) Supervisor crea Asignación (plan del día/turno).
2) Supervisor agrega ítems planificados (OT/artículo/operación/máquina/objetivos) y asigna operarios.
3) Supervisor publica la asignación (congela snapshot de estándares).
4) Operario carga su Parte (WorkLog) con Entradas (WorkLogEntry):
   - productivas: tiempo + unidades
   - no productivas: tiempo + concepto
5) Operario envía para revisión (Submitted).
6) Supervisor revisa y aprueba (Reviewed) o devuelve para corrección.
7) Cierre final (Locked) para auditoría.

## 4. Actores (roles conceptuales)
- Operario
- Supervisor
- Administrador / Analista

IMPORTANTE:
La aplicación NO define restricciones de menú “a mano”.
El acceso a pantallas/acciones se controla por el sistema de seguridad del proyecto
(definido en el chat "02-Diseño Diccionario BD").
Este módulo solo declara:
- acciones disponibles
- permisos requeridos para cada acción
y el esquema de seguridad decide quién las puede ejecutar.

## 5. Cálculos principales
- ProductiveMinutes = suma minutos de entradas con conceptos productivos
- NonProductiveMinutes = suma minutos de entradas con conceptos no productivos
- TheoreticalUnits = ProductiveMinutes/60 * StdUnitsPerHour (snapshot)
- EfficiencyPct = UnitsDone / TheoreticalUnits (si TheoreticalUnits > 0)

## 6. Dimensiones típicas para reportes
- Fecha, turno, operario
- OT, artículo, operación, máquina
- Tipo de tarea
- Concepto de tiempo (productivo/no productivo)
- Supervisor que asignó y/o revisó

## 7. Entregable esperado de la IDE (siguiente paso)
- Historias de usuario Must/Should
- Criterios de aceptación y casos borde
- Tickets técnicos (API, DB, UI)
- Propuesta de endpoints
- Validaciones y reglas de estado

Fin Documento.