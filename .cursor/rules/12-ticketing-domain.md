# 04 — Dominio: Sistema de Partes de Atención (tickets)

## 1) Entidades mínimas (conceptuales)
- Ticket (Parte)
- TicketComment (Seguimiento)
- TicketAttachment (Adjuntos)
- TicketStatusHistory (Historial de estados)
- Customer (Cliente)
- User/Agent (Atención)
- Catálogos: Category/Type/Priority

## 2) Estados (alineado a tu práctica)
- RECIBIDO
- PENDIENTE_DE_REALIZACION
- EN_PROCESO
- DERIVADO_A_TERCEROS
- CONCLUIDO_CON_EXITO
- CONCLUIDO_SIN_EXITO

## 3) Reglas de transición (principio)
- Definir transiciones válidas (no permitir saltos arbitrarios).
- Cambio de estado siempre debe:
  - registrar auditoría (actor + timestamp)
  - registrar estado anterior/nuevo
  - (opcional) requerir comentario según transición (cierre/derivación)

## 4) Auditoría mínima por ticket
- created_by, created_at
- updated_by, updated_at
- assigned_to (si aplica)
- historial de estados (eventos)

## 5) Endpoints (conceptuales, no implementar todavía)
- CRUD Ticket
- agregar comentario
- adjuntar archivo
- cambiar estado
- asignar agente
- listados con filtros (cliente, estado, fechas, prioridad, texto)

---
