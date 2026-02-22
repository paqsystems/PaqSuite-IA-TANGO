---
alwaysApply: false
---
# Regla: Testing – Estrategia del MVP

## Objetivo
Garantizar que el flujo E2E principal funcione correctamente y que la lógica
de negocio básica sea confiable, sin sobre–dimensionar la estrategia de testing.

---

## Convención: dos tipos de test por tarea/historia

**Cada vez que se implemente una tarea o historia (TR/HU), se deben añadir los dos tipos de test en frontend:**

| Tipo | Herramienta | Ubicación | Qué valida |
|------|-------------|-----------|------------|
| **Unitarios** | Vitest | `frontend/src/**/*.{test,spec}.{ts,tsx}` | Lógica, servicios, utilidades, componentes aislados. |
| **E2E** | Playwright | `frontend/tests/e2e/*.spec.ts` | Flujo de usuario en navegador (login, pantallas, formularios). |

**Ejecución recomendada al cerrar una tarea:** correr ambos con un solo comando (ver más abajo).

**Checklist al programar próximos tests (por tarea/historia):**
1. ¿Hay lógica o servicio en frontend? → Añadir o ampliar tests en `src/` (Vitest).
2. ¿Hay pantalla o flujo que el usuario recorre? → Añadir o ampliar spec en `tests/e2e/` (Playwright).
3. Al terminar la tarea → Ejecutar `npm run test:all` en `frontend/` (y `php artisan test` en backend si aplica).

---

## Tipos de Tests

### Tests Unitarios
**Propósito:** Validar funciones y lógica de negocio aislada.

**Cobertura esperada:**
- Funciones críticas (validaciones, reglas de negocio).
- No se persigue cobertura total, sino cobertura significativa.

**Ejemplos:**
- Validación de duración > 0.
- Validación de fecha no futura.
- Asociación correcta usuario–tarea.

---

### Tests de Integración
**Propósito:** Verificar la interacción entre API y base de datos.

**Ejemplos:**
- Crear tarea y persistirla.
- Listar tareas de un usuario.
- Evitar acceso a tareas de otros usuarios.

---

### Test End-to-End (E2E)
**Propósito:** Validar el flujo principal desde la perspectiva del usuario.

**Flujo cubierto:**
Login → Registro de tarea → Visualización de tareas.

**Herramienta instalada y configurada:**
- ✅ **Playwright** (instalado y configurado en `frontend/`)
- Configuración: `frontend/playwright.config.ts`
- Tests ubicados en: `frontend/tests/e2e/`
- Documentación: `docs/frontend/testing.md` y `.cursor/rules/11-playwright-testing-rules.md`

**Ejecutar tests en frontend:**
```bash
cd frontend
# Opción recomendada al cerrar una tarea: unitarios + E2E
npm run test:all             # Vitest (run) + Playwright E2E

# Por separado
npm run test                 # Vitest (modo watch)
npm run test:run             # Vitest una sola vez
npm run test:e2e             # Solo Playwright E2E
npm run test:e2e:ui          # E2E con UI interactiva
npm run test:e2e:headed      # E2E con navegador visible
```

**Ejecutar tests en backend:**
```bash
cd backend
php artisan test
```

**Ejecutar todo (backend + frontend unitarios + frontend E2E):**
```bash
cd backend && php artisan test && cd ../frontend && npm run test:all
```

---

## Alcance del Testing
- Se prioriza el flujo E2E.
- No se incluyen tests de carga.
- No se incluyen pruebas de seguridad avanzadas.
- No se automatiza visual regression.

### Estado vacío en consultas (HU-050 / TR-050)
En las pantallas de consulta (Consulta Detallada, Tareas por Cliente), cuando no hay resultados se muestra un mensaje informativo ("No se encontraron tareas para los filtros seleccionados") en lugar de tabla o lista vacía. Los E2E en `consulta-detallada.spec.ts` y `tareas-por-cliente.spec.ts` incluyen escenarios que verifican este mensaje cuando el período no tiene datos (p. ej. período 2030).

### Dashboard (TR-051)
Los E2E en `dashboard.spec.ts` requieren que el **backend esté en marcha** (`php artisan serve` en `backend/`) para que las pruebas que consumen GET /api/v1/dashboard pasen. Si el backend no está disponible, el dashboard mostrará mensaje de error y los tests que exigen KPIs fallarán.

---

## Estructura de Tests (Ejemplo)

### Backend
backend/
tests/
  Unit/          # Tests unitarios (lógica de negocio, servicios)
  Feature/       # Tests de integración (API + base de datos)


### Frontend
frontend/
tests/
  unit/
  e2e/              # Tests E2E con Playwright (✅ configurado)
    example.spec.ts  # Test de ejemplo
    README.md        # Documentación de tests E2E
playwright.config.ts  # Configuración de Playwright

---

## Criterios de Aceptación de Tests
- Todos los tests pasan localmente.
- El test E2E del flujo principal pasa en CI.
- Los tests documentados pueden ejecutarse siguiendo instrucciones claras.

---

## Integración con CI/CD
El pipeline debe:
1. Ejecutar tests unitarios.
2. Ejecutar tests de integración.
3. Ejecutar al menos un test E2E.

---

## Notas
- El alcance de tests es coherente con un MVP.
- La estrategia puede ampliarse en etapas posteriores.
- Las decisiones de testing forman parte del diseño del producto.
