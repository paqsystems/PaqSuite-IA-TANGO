# 00 — Política general para Cursor (NO programar todavía)

## Objetivo
Definir reglas obligatorias para el BackEnd de todo el proyecto usando **Laravel + PHP** con **Laravel Sanctum** para autenticación.

## Alcance
Estas reglas aplican a:
- Todas las APIs (GET/POST/PUT/PATCH/DELETE)
- Servicios, validaciones, manejo de errores, seguridad, logging/auditoría
- ORM (Eloquent) y consideraciones para consultas SQL complejas

## Restricción principal
- **NO escribir código** (ni controladores, ni migrations, ni modelos).
- En esta fase Cursor solo debe producir:
  - documentación
  - definiciones
  - especificaciones
  - checklists
  - convenciones
  - ejemplos de payloads (sin implementar endpoints reales)

## Principios
- Consistencia > creatividad.
- Seguridad por defecto.
- Validar inputs antes de persistir.
- No exponer detalles internos (stacktrace, SQL, credenciales).
- Mantener contrato API estable (evitar breaking changes).

## Entregables esperados en esta fase
- Contrato base de API (respuesta, errores, paginación, filtros, versionado).
- Playbook BackEnd Laravel (arquitectura, capas, validación, errores, logging).
- Especificación del dominio “Partes/Tickets” (estados, transiciones, entidades).
- Checklists para TDD (seguridad, contrato API, config BD, etc.).

---
