
# Modelo de Datos Lógico — Sistema de Partes de Atención
**Estado:** Borrador v1 (sin implementación)  
**Objetivo:** Definir entidades, atributos clave, relaciones y enumeraciones mínimas para poder especificar APIs y procesos sin romper contrato más adelante.

> Este documento es **lógico** (conceptual). No define tipos SQL exactos, índices físicos ni migraciones.  
> No se programa nada todavía.

---

## 0) Convenciones generales

### Identificadores
- Todas las entidades tienen `id` (UUID).  

### Auditoría estándar (para entidades principales)
- `created_at`, `updated_at` (timestamps)
- `created_by_user_id`, `updated_by_user_id` (FK a User) cuando aplique
- `deleted_at` (soft delete) **solo si** el negocio lo requiere

### Multi-instalación / multi-cliente (tenancy)
Elegir uno de estos enfoques (definir en etapa posterior, pero dejar previsto):
- **A) Single-tenant por instalación** (cada cliente con su BD).  
  - No se requiere `tenant_id`.

**Decisión recomendada por simplicidad operativa:** A) Single-tenant por instalación.  
Este documento asume A), pero deja campos opcionales marcados como `(tenant)`.

---

## 1) Enumeraciones (catálogos)

### 1.1 TicketStatus (Estados)
Valores (alineado a tu práctica):
- `RECIBIDO`
- `PENDIENTE_DE_REALIZACION`
- `EN_PROCESO`
- `DERIVADO_A_TERCEROS`
- `CONCLUIDO_CON_EXITO`
- `CONCLUIDO_SIN_EXITO`

**Notas:**
- El estado actual vive en `Ticket.current_status` y además se registra historial en `TicketStatusHistory`.

### 1.2 TicketPriority (Prioridad)
- `BAJA`
- `MEDIA`
- `ALTA`
- `URGENTE`

### 1.3 TicketChannel (Canal de ingreso)
- `EMAIL`
- `WHATSAPP`
- `TELEFONO`
- `WEB`
- `INTERNO`
- `OTRO`

### 1.4 TicketResolution (Tipo de resolución) *(opcional)*
- `EXITO`
- `SIN_EXITO`
- `PENDIENTE`
> Puede derivarse del estado final; mantenerlo solo si aporta reportes.

### 1.5 AttachmentType *(opcional)*
- `IMAGEN`
- `PDF`
- `TEXTO`
- `OTRO`

---

## 2) Entidades principales

## 2.1 User (Usuario)
Representa usuarios internos (empleados) y (opcionalmente) usuarios externos (clientes).

**Campos:**
- `id`
- `name`
- `email` (único)
- `password_hash` (si aplica login local)
- `is_active` (bool)
- `role` (o relación a roles/permisos)
- `last_login_at` (opcional)
- `created_at`, `updated_at`
- `(tenant_id)` opcional

**Relaciones:**
- User (1) — (N) Tickets creados (`Ticket.created_by_user_id`)
- User (1) — (N) Tickets asignados (`Ticket.assigned_to_user_id`)
- User (1) — (N) Comentarios (`TicketComment.user_id`)
- User (1) — (N) Historial de estados (`TicketStatusHistory.changed_by_user_id`)

**Notas:**
- Autenticación será vía **Sanctum** (tokens personales o SPA cookies).  
- Roles/permisos: se define en etapa posterior (posible `spatie/laravel-permission`).

---

## 2.2 Customer (Cliente)
Empresa/cliente al que se le brinda soporte.

**Campos:**
- `id`
- `code` (opcional, interno)
- `name` (razón social / nombre)
- `tax_id` (CUIT, opcional)
- `email` (opcional)
- `phone` (opcional)
- `address` (opcional)
- `is_active` (bool)
- `created_at`, `updated_at`
- `(tenant_id)` opcional

**Relaciones:**
- Customer (1) — (N) Tickets (`Ticket.customer_id`)

---

## 2.3 Ticket (Parte)
Entidad central.

### Campos mínimos (actualizado v2.1):

- `id`
- `customer_id` (FK Customer) **obligatorio**
- `title` **obligatorio**
- `description` **obligatorio**
- `channel` (TicketChannel) **obligatorio**
- `priority` (TicketPriority) **obligatorio**
- `current_status` (TicketStatus) **obligatorio**

### Asignación y autoría
- `assigned_to_agent_profile_id` (FK AgentProfile) *(nullable)*
- `created_by_account_id` (FK Account) *(nullable si ingresa por integración técnica)*

### Clasificación operativa / comercial
- `is_non_billable` (boolean) **obligatorio**
  - `true`  → tarea **sin cargo** para el cliente  
  - `false` → tarea facturable (por defecto)
- `is_on_site` (boolean) **obligatorio**
  - `true`  → tarea **presencial** (en el cliente)
  - `false` → tarea **remota** (por defecto)

### Referencias y tiempos
- `external_reference` (string, opcional: id mail/whatsapp, ticket externo, etc.)
- `opened_at` (timestamp; default = created_at)
- `closed_at` (timestamp, nullable)

### Cierre
- `resolution_summary` (texto, nullable; **requerido** en ciertos cierres)

### Auditoría
- `created_at`, `updated_at`
- `deleted_at` (opcional)

---

### Reglas lógicas asociadas

- `closed_at` solo puede tener valor si:
  - `current_status` ∈ {`CONCLUIDO_CON_EXITO`, `CONCLUIDO_SIN_EXITO`}
- Cambios de `current_status` deben generar un registro en `TicketStatusHistory`.
- `is_non_billable = true`:
  - indica que la tarea **no debe incluirse** en procesos de facturación.
- `is_on_site = true`:
  - puede implicar reglas adicionales en reportes, SLA o costos (a definir en procesos).

**Relaciones:**
- Ticket (1) — (N) TicketComment
- Ticket (1) — (N) TicketAttachment
- Ticket (1) — (N) TicketStatusHistory
- Ticket (N) — (1) Customer
- Ticket (N) — (0..1) User asignado
- Ticket (N) — (0..1) User creador
---

## 2.4 TicketComment (Seguimiento / Comentarios)
Registra notas, respuestas, avances.

**Campos:**
- `id`
- `ticket_id` (FK Ticket) **obligatorio**
- `user_id` (FK User) *(nullable si viene de integración sin user interno)*
- `body` (texto) **obligatorio**
- `is_internal` (bool; comentario interno vs visible al cliente) **default: true**
- `source` (enum TicketChannel u otro; opcional)
- `created_at`, `updated_at`
- `(tenant_id)` opcional

**Relaciones:**
- TicketComment (N) — (1) Ticket
- TicketComment (N) — (0..1) User

---

## 2.5 TicketAttachment (Adjuntos)
Archivos adjuntos a ticket o a comentarios.

**Campos:**
- `id`
- `ticket_id` (FK Ticket) **obligatorio**
- `comment_id` (FK TicketComment) *(nullable; si el adjunto pertenece a un comentario específico)*
- `uploaded_by_user_id` (FK User) *(nullable)*
- `original_filename`
- `stored_filename` (o `storage_key`)
- `mime_type`
- `size_bytes`
- `checksum` (opcional)
- `storage_provider` (ej. local/s3; opcional)
- `storage_path` (o bucket/key)
- `created_at`, `updated_at`
- `(tenant_id)` opcional

**Relaciones:**
- TicketAttachment (N) — (1) Ticket
- TicketAttachment (N) — (0..1) TicketComment

**Notas:**
- No se expone `storage_path` al frontend sin control; se entrega URL firmada o endpoint protegido.

---

## 2.6 TicketStatusHistory (Historial de estados)
Cada transición de estado genera un evento.

**Campos:**
- `id`
- `ticket_id` (FK Ticket) **obligatorio**
- `from_status` (TicketStatus) *(nullable solo para “creación”)*
- `to_status` (TicketStatus) **obligatorio**
- `changed_by_user_id` (FK User) *(nullable si evento automático)*
- `change_reason` (texto, nullable; requerido en ciertas transiciones: derivación/cierre sin éxito)
- `created_at`
- `(tenant_id)` opcional

**Relaciones:**
- TicketStatusHistory (N) — (1) Ticket
- TicketStatusHistory (N) — (0..1) User

---

## 3) Catálogos opcionales (según necesidad)

## 3.1 TicketCategory (Categoría)
Para clasificar tickets.

**Campos:**
- `id`
- `name`
- `is_active`
- `created_at`, `updated_at`
- `(tenant_id)` opcional

Relación:
- Ticket (N) — (0..1) TicketCategory (por `category_id` en Ticket)

## 3.2 TicketType (Tipo)
Ej.: “Bug”, “Consulta”, “Mejora”, “Capacitación”.

---

## 4) Relaciones resumidas (cardinalidad)

- Customer 1 — N Ticket
- Ticket 1 — N TicketComment
- Ticket 1 — N TicketAttachment
- Ticket 1 — N TicketStatusHistory
- User 1 — N Ticket (creados)
- User 1 — N Ticket (asignados)
- User 1 — N TicketComment
- User 1 — N TicketStatusHistory
- TicketComment 1 — N TicketAttachment *(si se decide adjuntar al comentario)*

---

## 5) Campos “sensibles” y seguridad

Campos sensibles (no deben exponerse en APIs):
- `password_hash`
- `storage_path`/`storage_key` si revela estructura interna
- cualquier campo de configuración interna

Campos con potencial de inyección:
- `sort`, `filters`, `search` (si se implementan búsquedas)
> Deben validarse contra whitelist.

---

## 6) Análisis mínimo para reportes (futuro)

Para métricas:
- Tickets por estado, por cliente, por empleado
- Tiempo hasta primera respuesta (si se implementa)
- Tiempo hasta cierre
- Cantidad de derivaciones
- Concluido con/ sin éxito

De ser necesario, se agregan:
- `first_response_at`
- `sla_due_at`
- `reopened_at` / `reopen_count`

---

## 7) Pendientes de decisión (para cerrar en fase siguiente)

1) Single-tenant por instalación (recomendado) 
2) Identificadores: UUID
3) ¿Comentarios solo internos? (ya contemplado con `is_internal`)
4) ¿Se permite reabrir tickets? (impacta estados y transiciones). SI
5) ¿Adjuntos solo al ticket o también a comentarios? (contemplado con `comment_id` nullable). Sólo al Ticket
6) ¿Roles/permisos (spatie) o simple role string? Roles/permisos (spatie)

---

# FIN — DATA_MODEL.md
