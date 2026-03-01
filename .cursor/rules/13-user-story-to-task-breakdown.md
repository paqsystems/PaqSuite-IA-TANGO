# Regla: Descomposición de Historia de Usuario a Tareas 

## Objetivo
Dada UNA Historia de Usuario (HU), generar un plan de trabajo ejecutable:
- lista de tareas/tickets con alcance claro,
- orden sugerido,
- criterios de aceptación,
- impacto en datos, backend, frontend, tests, docs, CI/CD,
- alineado a las consignas del Trabajo Final.

## Inputs obligatorios
Para cada HU recibida, asumir que se provee (o pedir que se provea) como mínimo:
- Título
- Narrativa (Como <rol> quiero <acción> para <beneficio>)
- Alcance (in/out)
- Prioridad (MUST o SHOULD)
- Rol(es) involucrados: Usuario Supervisor / Usuario operador

## Output requerido (formato)
Responder siempre con estas secciones en este orden:

### 0) Tabla de metadatos (encabezado del TR)
Incluir **antes** de "1) HU Refinada" una tabla con:

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-XXX – Título de la HU                   |
| Épica              | 000 – Nombre de la épica                   |
| Prioridad          | MUST-HAVE / SHOULD-HAVE                    |
| Roles              | Rol(es) involucrados                        |
| Dependencias       | HUs o TRs de los que depende               |
| Clasificación      | HU SIMPLE / HU COMPLEJA                    |
| Última actualización | YYYY-MM-DD                               |
| Estado             | Pendiente / Implementado                  |

Línea de **Origen:** enlace al archivo HU.

---

### 1) HU Refinada
- Título
- Narrativa
- Contexto/objetivo
- Suposiciones explícitas (si faltan datos)
- In scope / Out of scope

### 2) Criterios de Aceptación (AC)
- 6 a 12 AC en bullets

**Escenarios Gherkin (obligatorio):** Incluir subsección `### Escenarios Gherkin` con bloques en sintaxis Gherkin:

```gherkin
Feature: Nombre del feature

  Scenario: Descripción del escenario
    Given condición inicial
    When acción del usuario
    Then resultado esperado

  Scenario: Otro escenario
    ...
```

- Incluir 3 a 6 escenarios que cubran los flujos principales (éxito, errores, permisos, edge cases).
- Usar `Feature`, `Scenario`, `Given`, `When`, `Then` (y `And` cuando aplique).

### 3) Reglas de Negocio
- Lista numerada
- Incluir permisos por rol (usuario supervisor / usuario operador)
- Incluir validaciones y restricciones de estado

### 4) Impacto en Datos
- Tablas afectadas (USERS, PQ_*, convenciones del proyecto)
- Nuevas columnas/índices/constraints
- Migración + rollback
- Seed mínimo para tests

### 5) Contratos de API (si aplica)
- Endpoints (método + path)
- Request/Response schema (JSON)
- Códigos de error (400/401/403/404/409/422/500 según corresponda)
- Autenticación/autorización (qué rol accede)

### 6) Cambios Frontend (si aplica)
- Pantallas/componentes afectados
- Estados UI (loading/empty/error/success)
- Validaciones en UI
- Accesibilidad mínima (roles/labels)
- Selectores de test: agregar `data-testid` en elementos clave

### 7) Plan de Tareas / Tickets
Generar una lista de tareas con:
- ID (T1, T2, …)
- Tipo: DB / Backend / Frontend / Tests / Docs / DevOps
- Descripción breve
- DoD específico
- Dependencias (si aplica)
- Estimación relativa (S/M/L) opcional

**Cobertura mínima obligatoria por HU (si aplica):**
- DB: migración + seed
- Backend: endpoints + validaciones + manejo de errores
- Frontend: UI + validaciones + estados
- Tests:
  - unit tests (negocio/servicios)
  - integration tests (API)
  - al menos 1 E2E Playwright para el flujo
- Docs:
  - OpenAPI/Swagger actualizado si hay endpoints
  - README o docs/actualizadas si cambia comportamiento
- DevOps:
  - si impacta despliegue/secretos/config: ajustar CI/CD o variables

### 8) Estrategia de Tests (Playwright y otros)
- Unit: qué funciones/reglas cubrir
- Integration: qué endpoints y casos (success + errores)
- E2E: flujo real, sin waits ciegos:
  - navegar al punto inicial
  - interacciones reales (click/type/select)
  - `expect(...)` sobre estados visibles
  - selectores estables (data-testid / roles)
  - evidencias en fallos (screenshot/trace/video según config)

### 9) Riesgos y Edge Cases
- Concurrencia/duplicados
- Permisos
- Datos incompletos
- Estados intermedios
- Performance (si aplica)

### 10) Checklist final (para validar HU terminada)
- [ ] AC cumplidos
- [ ] Migración + rollback + seed
- [ ] Backend listo + errores consistentes
- [ ] Frontend listo + estados UI
- [ ] Unit tests ok
- [ ] Integration tests ok
- [ ] ≥1 E2E Playwright ok (sin waits ciegos)
- [ ] Docs/OpenAPI actualizados
- [ ] IA log actualizado
- [ ] CI/CD pasa

## Reglas de estilo (importante)
- No inventar funcionalidades fuera del scope.
- Si falta info crítica, declarar supuestos y marcar preguntas abiertas.
- Mantener coherencia con la arquitectura y convenciones del repo.
- Si se modifica código existente, listar impacto en docs y tests asociados.
