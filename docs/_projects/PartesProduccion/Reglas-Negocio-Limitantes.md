# Documento 3 (v2) — Reglas de Negocio y Limitantes
Este documento NO asume conocimiento previo del sistema existente.

## 1. Reglas de estados — Asignación
Estados:
- Draft: editable.
- Published: visible para operarios; congela snapshot en ítems.
- Closed: solo lectura.
- Cancelled: no utilizada.

Regla: Solo usuarios con permiso correspondiente pueden publicar/cerrar/cancelar.
(El control de permisos lo provee el esquema de seguridad del proyecto, no hardcode en menús.)

## 2. Reglas de estados — WorkLog (parte por operario)
Estados:
- Open: editable por el operario.
- Submitted: enviado a revisión; bloqueado para operario.
- Reviewed: aprobado por supervisor (o “devuelto” según regla).
- Locked: cierre definitivo (auditoría).

Regla: Solo usuarios con permiso de revisión pueden pasar Submitted->Reviewed/Locked.
Regla: Devolver para corrección implica volver a Open (o un estado “Returned” si se decide agregar).

## 3. Reglas de captura de tiempo
Modalidades admitidas:
A) Intervalo: StartAt/EndAt.
B) Duración directa: DurationMinutes.

Reglas:
- Si StartAt y EndAt existen => EndAt > StartAt.
- DurationMinutes se calcula en backend si hay intervalo.
- (Configurable) No se permiten solapamientos de intervalos para un mismo operario en el mismo día/turno.

## 4. Reglas de conceptos productivos/no productivos
- Cada entrada requiere TimeConceptId.
- Si el concepto es NO productivo:
  - UnitsDone/UnitsScrap/UnitsRework deben ser null o 0 (no se registran unidades).
- Si el concepto es productivo:
  - UnitsDone puede ser requerido según configuración.

## 5. Reglas de relación con asignación
- Una entrada puede referenciar AssignmentItemId (ideal).
- Opcionalmente se permite carga directa sin asignación previa (feature flag).

En revisión:
- Supervisor puede reclasificar una entrada (cambiar AssignmentItemId / OT / máquina / operación / concepto),
dejando observación.

## 6. Reglas de congelamiento de estándar (snapshot)
- Al publicar asignación:
  - StdUnitsPerHour del ítem se congela (snapshot).
- Cambios posteriores en el catálogo NO alteran el cálculo histórico de esa asignación.

## 7. Reglas de cálculo de eficiencia
- TheoreticalUnits = ProductiveMinutes/60 * StdUnitsPerHour(snapshot)
- EfficiencyPct = UnitsDone / TheoreticalUnits si TheoreticalUnits > 0
- Si no hay StdUnitsPerHour => eficiencia null (no calculable)

## 8. Limitantes explícitas del módulo
- No stock
- No depósitos
- No lotes
- No consumos
- No BOM
- No MRP
- No asientos contables

Se admite “valorización operaria” solo como KPI analítico (si se decide):
- ValorHora por legajo/periodo para estimar costo interno,
sin impacto contable.

## 9. Feature flags recomendados
- Permitir carga directa sin asignación (Sí/No)
- Obligar UnitsDone en productivo (Sí/No)
- Obligar MachineId/OperationId en productivo (Sí/No)
- Control de solapamiento de intervalos (Sí/No)
- Modo de tiempo: duración / intervalo / ambos
- Ventana de edición operario post-turno (Sí/No + horas)

Fin Documento.