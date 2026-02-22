# Pull Request: Dashboard TR-053 a TR-055 + Documentación OpenAPI

## Título sugerido
**feat(dashboard): TR-053 a TR-055 – Dedicación por empleado, gráficos Recharts, actualización automática y generación OpenAPI/Swagger**

---

## Descripción

Este PR implementa las historias **Should-Have** TR-053, TR-054 y TR-055 del dashboard: resumen de dedicación por empleado (supervisor), gráficos con Recharts y actualización automática con indicador de última actualización. **Además, incluye la generación de documentación OpenAPI/Swagger** (no estaba en el PR anterior): L5-Swagger en el backend, UI en `/api/documentation`, anotaciones y reglas para mantener la doc al día.

## Tickets / Historias

| Ticket / Entregable | Descripción |
|---------------------|-------------|
| **TR-053 (SH)** | Resumen de dedicación por empleado en dashboard supervisor |
| **TR-054 (SH)** | Gráficos y visualizaciones en dashboard |
| **TR-055 (SH)** | Actualización automática del dashboard |
| **OpenAPI/Swagger** | Generación y publicación de documentación API (L5-Swagger, `/api/documentation`) |

## Cambios principales

### Backend
- **ReportController / Dashboard:** el endpoint de resumen incluye `porcentaje` en `top_empleados` para la sección de dedicación por empleado.
- **TaskService:** ajustes menores si aplican para consistencia con el dashboard.
- **Documentación OpenAPI / generación Swagger (nuevo en este PR):**
  - Paquete **L5-Swagger** (`darkaonline/l5-swagger`), config en `config/l5-swagger.php`.
  - Anotaciones base en `app/OpenApi.php` y `app/OpenApiPaths.php`.
  - Comando de generación: `php artisan l5-swagger:generate` → actualiza `storage/api-docs/api-docs.json`.
  - UI Swagger accesible en **`/api/documentation`** (con backend en marcha).
  - Vistas vendor en `resources/views/vendor/l5-swagger/`.
- **.env.example:** variables necesarias para documentación API (si se añadieron).

### Frontend
- **Dashboard.tsx / Dashboard.css:**
  - Sección **Dedicación por Empleado** (solo rol supervisor) con enlace "Ver detalle" a `/informes/tareas-por-empleado?usuario_id=...`.
  - Gráficos por cliente, por empleado (supervisor) y por tipo usando Recharts.
  - Actualización automática cada 5 minutos, indicador "Actualizado hace X min" y botón "Actualizar"; limpieza de intervalos al desmontar (TR-055).
- **GraficoDistribucion.tsx:** componente reutilizable con BarChart (Recharts) para distribución por cliente/empleado/tipo.
- **TareasPorEmpleadoPage.tsx:** lectura de `usuario_id`, `fecha_desde`, `fecha_hasta` desde la URL para el enlace desde el dashboard.
- **task.service.ts:** llamadas necesarias para datos del dashboard (resumen, top empleados con porcentaje).

### Tests
- **E2E (Playwright):** `frontend/tests/e2e/dashboard.spec.ts` ampliado con casos para TR-053 (sección Dedicación por Empleado y enlace a tareas-por-empleado) y TR-055 (botón Actualizar e indicador de última actualización). Los escenarios existentes cubren TR-051/052 y la visibilidad de gráficos (TR-054).

### Documentación
- **docs/hu-tareas:** añadidos TR-053(SH), TR-054(SH) y TR-055(SH) con criterios de aceptación.
- **.cursor/Docs:** documentación de `GraficoDistribucion.tsx` y de las tres historias.
- **docs/api/openapi.md** y **.cursor/rules/06-openapi-documentacion.md:** documentación y reglas para OpenAPI.
- **docs/ia-log.md:** registro de uso de IA en estos cambios.
- Limpieza de documentación obsoleta en `.cursor/Docs`.

## Cómo probar

1. **Entorno:** Backend (`php artisan serve`) y frontend (`npm run dev`) en ejecución.
2. **E2E dashboard:**  
   `cd frontend && set PLAYWRIGHT_BASE_URL=http://localhost:<puerto_vite> && npx playwright test tests/e2e/dashboard.spec.ts --project=chromium`
3. **Unitarios:**  
   `cd frontend && npm run test -- --run`
4. **Manual:** Iniciar sesión como supervisor, abrir Dashboard y comprobar: sección Dedicación por Empleado, enlace "Ver detalle", gráficos (cliente/empleado/tipo), botón "Actualizar" e indicador de última actualización; verificar actualización automática tras 5 min.
5. **OpenAPI:** Con el backend en marcha, abrir `http://localhost:8000/api/documentation` y comprobar que carga la UI de Swagger. Regenerar con `cd backend && php artisan l5-swagger:generate` si se cambian endpoints.

## Checklist (autor)

- [x] Criterios de aceptación de TR-053, TR-054 y TR-055 cubiertos
- [x] Tests E2E del dashboard ejecutados y pasando
- [x] Tests unitarios (Vitest) ejecutados y pasando
- [x] Documentación en `docs/hu-tareas` y `.cursor/Docs` actualizada
- [x] Sin commit de credenciales; cambios alineados con AGENTS.md y convenciones del proyecto
- [x] camelCase en variables, propiedades y funciones (frontend/backend)
- [x] Limpieza de intervalos/timers al desmontar (TR-055)
- [x] Documentación OpenAPI: L5-Swagger configurado, `l5-swagger:generate` ejecutado, UI en `/api/documentation` y docs en `docs/api/openapi.md` y `.cursor/rules/06-openapi-documentacion.md`

## Checklist de revisión (reviewer)

- [ ] **TR-053:** Como supervisor, la sección "Dedicación por Empleado" es visible y "Ver detalle" lleva a `/informes/tareas-por-empleado` con `usuario_id` correcto.
- [ ] **TR-054:** Gráficos visibles por rol (por cliente para todos; por empleado y por tipo según rol). Sin errores de consola de Recharts.
- [ ] **TR-055:** Botón "Actualizar" y texto "Actualizado hace X min" visibles; al hacer clic se refrescan datos; tras ~5 min los datos se actualizan solos (opcional verificar).
- [ ] **E2E:** `npx playwright test tests/e2e/dashboard.spec.ts` pasa con backend y frontend en marcha.
- [ ] **Unitarios:** `npm run test -- --run` en `frontend/` pasa.
- [ ] No se introducen regresiones en login, roles ni en otras pantallas del dashboard (TR-051, TR-052).
- [ ] **OpenAPI:** UI en `http://<backend>/api/documentation` carga correctamente; `php artisan l5-swagger:generate` se ejecuta sin errores.
- [ ] Documentación en `docs/hu-tareas` y `.cursor/Docs` es coherente con el código.

---

**Rama:** `finalproject-PAQ`  
**Commit:** `feat(dashboard): TR-053 a TR-055 - Dedicación por empleado, gráficos Recharts y actualización automática`
