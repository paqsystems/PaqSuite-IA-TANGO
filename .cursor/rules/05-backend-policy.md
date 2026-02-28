# 00 — Política general para Backend

## Objetivo
Definir reglas obligatorias para el BackEnd de todo el proyecto usando **Laravel + PHP** con **Laravel Sanctum** para autenticación.

## Alcance
Estas reglas aplican a:
- Todas las APIs (GET/POST/PUT/PATCH/DELETE)
- Servicios, validaciones, manejo de errores, seguridad, logging/auditoría
- ORM (Eloquent) y consideraciones para consultas SQL complejas

## Principios
- Consistencia > creatividad.
- Seguridad por defecto.
- Validar inputs antes de persistir.
- No exponer detalles internos (stacktrace, SQL, credenciales).
- Mantener contrato API estable (evitar breaking changes).

s## Referencias
- `docs/01-arquitectura/01-arquitectura-proyecto.md` - Arquitectura por capas
- `docs/backend/PLAYBOOK_BACKEND_LARAVEL.md` - Convenciones Laravel
- `docs/domain/DATA_MODEL.md` - Modelo de datos
- `.cursor/rules/06-api-contract.md` - Contrato API
- `.cursor/rules/08-security-sessions-tokens.md` - Seguridad y tenant

---
