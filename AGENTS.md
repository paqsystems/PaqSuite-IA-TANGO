
# AGENTS.md – Guía del proyecto para el Agente (Cursor / IA)

## 1) Contexto
Este repositorio contiene el desarrollo de un **MVP web** para consultorías y empresas de servicios.

El sistema permite que cada empleado/asistente registre tareas diarias indicando:
- Fecha
- Cliente
- Proyecto 
- Descripción de la tarea
- Duración ( en lapsos de 15 minutos) 

Con esto se obtienen **informes de dedicación por cliente/proyecto** para análisis operativo, comercial y/o facturación.

> Regla de oro del MVP: priorizar valor completo del flujo E2E, sin sobre–ingeniería.

---

## 2) Consignas obligatorias (resumen)
El proyecto debe cumplir:
1. Definir un flujo E2E prioritario con principio/fin claros.
2. Planificar ese flujo con:
   - 3–5 historias Must-Have
   - 1–2 historias Should-Have
3. Producir artefactos:
   - Documentación de producto
   - Historias + tickets trazables
   - Arquitectura + modelo de datos
   - Backend (API + DB)
   - Frontend navegable
   - Tests (unit + integración + al menos 1 E2E)
   - Infra + deploy (CI/CD básico, secretos, URL accesible)

Ver detalle completo en: `docs/consignas-mvp.md`

---

## 3) Flujo E2E prioritario (MVP)
**(COMPLETAR cuando se defina)**

Ejemplo típico para este proyecto:
- Registro → Login → Carga de tarea → Ver resumen (por día/semana) → Logout

Regla: toda historia/ticket/código/test debe soportar este flujo.

---

## 4) Historias Must / Should
Definición y criterios de aceptación en:
- `docs/_projects/SistemaPartes/hu-historias/` (historias)
- `docs/_projects/SistemaPartes/hu-tareas/` (tareas técnicas)
Esto es un ejemplo de un solo módulo o proyecto que contemplará toda la solución

Reglas:
- Must-Have: indispensables para que el flujo E2E tenga valor completo.
- Should-Have: mejoras opcionales, solo si no ponen en riesgo lo Must.

---

## 5) Artefactos y dónde se mantienen
Este repo mantiene la documentación en `/docs`:

- `docs/00-contexto/`  
  Contexto institucional, onboarding, guías corporativas.

- `docs/01-arquitectura/`  
  Arquitectura técnica, modelo de datos, seguridad, roadmap (ver `docs/01-arquitectura/README.md`).

- `docs/_projects/SistemaPartes/`  
  Historias de usuario (hu-historias/) y tareas técnicas (hu-tareas/) del módulo Partes.

- `docs/arquitectura.md`  
  Visión general del sistema (3 capas, web+mobile).

- `.cursor/rules/12-testing.md`  
  Estrategia, qué se testea, cómo correr tests, y el/los E2E.

- `docs/06-operacion/deploy-infraestructura.md`  
  Infra, pipeline, secretos, URL, cómo desplegar local/remote.

---

## 6) Reglas de trabajo para el agente IA
### 6.1 Alcance y priorización
- Priorizar el flujo E2E.
- Evitar features “nice to have” hasta cerrar Must-Have.
- No agregar dependencias pesadas sin justificación.

### 6.2 Calidad mínima del código (MVP, pero profesional)
- Nombres claros, estructura simple.
- Validaciones básicas del dominio.
- Manejo de errores consistente.
- Logs mínimos donde aporten valor.

### 6.3 Tests (obligatorio)
- Unit tests: lógica de dominio/servicios.
- Integration tests: API + DB (o repositorio) con fixtures.
- E2E: al menos 1 caso del flujo principal.
- **Por cada tarea/historia en frontend:** añadir ambos tipos — unitarios (Vitest en `src/`) y E2E (Playwright en `tests/e2e/`). Al cerrar: ejecutar `npm run test:all` en `frontend/`.
- Mantener instrucciones y checklist en `.cursor/rules/12-testing.md`.

### 6.4 Trazabilidad
Cada cambio relevante debe dejar rastro:
- Referenciar historia/ticket en commits (si se usa git con convención).
- Actualizar el doc correspondiente en `/docs` cuando cambie el alcance o diseño.

---

## 7) Integraciones / MCP / automatización (opcional)
Si el proyecto usa MCP (Jira, Playwright, DB), documentar configuración y variables en:
- `docs/agentes-y-mcp.md` (si existiera)
o en:
- `docs/06-operacion/deploy-infraestructura.md` (si afecta ejecución)

Nunca incluir credenciales reales en el repo.
Usar `.env.example` con placeholders.

---

## 8) Definition of Done (DoD) del MVP
Un Must-Have se considera terminado cuando:
- Cumple criterios de aceptación
- Tiene tests correspondientes (unit o integración, según aplique)
- No rompe el E2E
- Está documentado lo necesario
