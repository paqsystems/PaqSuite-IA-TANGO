# TR-007 – Parámetros generales del sistema

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-007 – Parámetros generales del sistema  |
| Épica              | 000 – Generalidades                        |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Usuario con permiso de configuración       |
| Dependencias       | HU-001 (Login), HU-002 (Cambio empresa)   |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-007 – Parámetros generales del sistema](../../03-historias-usuario/000-Generalidades/HU-007-Parametros-generales.md)

---

## 1) HU Refinada

- **Título:** Parámetros generales del sistema
- **Narrativa:** Como usuario con permiso de configuración quiero editar los parámetros generales de cada módulo desde una pantalla dedicada para que el sistema se adapte a mi empresa sin modificar código.
- **Contexto:** Proceso reutilizable. Invocado desde ítems de menú; procedimiento = nombre clave del módulo. Solo editar valores; no agregar ni eliminar. Tabla en Company DB.
- **In scope:** Pantalla mantenimiento por módulo, filtro por Programa, edición Valor_* según tipo.
- **Out of scope:** ABM de claves (estructura en seeds por módulo).

---

## 2) Criterios de Aceptación

- Proceso general de mantenimiento PQ_PARAMETROS_GRAL.
- Invocado desde ítems de menú; PQ_MENUS.Procedimiento = nombre clave. Solo registros con Programa = procedimiento del ítem.
- No agregar ni eliminar; solo editar Valor_* según tipo_valor (string, int, datetime, bool, decimal, text).
- Consulta en base de datos de empresa activa (Company DB). Cada empresa tiene su instancia.
- Cada fila: Clave, descripción (label), campo valor editable según tipo. Validar tipos y rangos al guardar.
- Registros iniciales vía seed en deploys (como PQ_MENUS).

### Escenarios Gherkin

```gherkin
Feature: Parámetros generales del sistema

  Scenario: Usuario accede a parámetros de un módulo
    Given el usuario está autenticado
    And tiene empresa activa seleccionada
    And existe ítem de menú con procedimiento "PartesProduccion"
    When accede a la pantalla de parámetros para PartesProduccion
    Then ve la lista de parámetros del módulo (Clave, Valor)
    And cada fila tiene campo editable según tipo_valor

  Scenario: Usuario edita parámetro
    Given el usuario está en parámetros de PartesProduccion
    And existe el parámetro "descripcion_obligatoria" tipo bool
    When modifica el valor del parámetro
    And hace clic en "Guardar"
    Then el valor se persiste en Company DB
    And se muestra confirmación o actualización en pantalla

  Scenario: Parámetros por empresa activa
    Given el usuario tiene acceso a Empresa A y Empresa B
    When selecciona Empresa A y accede a parámetros PartesProduccion
    Then ve los valores de PQ_PARAMETROS_GRAL de la BD de Empresa A
    When cambia a Empresa B y accede a parámetros PartesProduccion
    Then ve los valores de PQ_PARAMETROS_GRAL de la BD de Empresa B

  Scenario: No agregar ni eliminar registros
    Given el usuario está en parámetros de un módulo
    Then no ve botones "Agregar" ni "Eliminar"
    And solo puede editar los valores de los parámetros existentes

  Scenario: Validación de tipo al guardar
    Given el usuario está en parámetros de PartesProduccion
    And el parámetro "duracion_minima_minutos" es tipo Int
    When ingresa un valor no numérico
    And hace clic en "Guardar"
    Then se muestra error de validación
    And el valor no se persiste
```

---

## 3) Reglas de Negocio

- Solo editar valores; claves definidas en seeds por módulo. Usuario con permiso para empresa activa. Proceso reutilizable por módulo.

---

## 4) Impacto en Datos

- Tabla PQ_PARAMETROS_GRAL (Company DB): Programa, Clave, tipo_valor, Valor_String, Valor_Text, Valor_Int, Valor_DateTime, Valor_Bool, Valor_Decimal.
- pq_menus (Diccionario): procedimiento vincula al proceso.
- Migración crear tabla en Company DB (o script por empresa). Seeds por módulo.

---

## 5) Contratos de API

- GET /api/parametros-gral?programa={programa}: listado parámetros del módulo (Company DB según empresa activa).
- PUT /api/parametros-gral/{programa}/{clave}: actualizar Valor_* según tipo. Validar tipos y rangos.
- Resolver Company DB según X-Company-Id.

---

## 6) Cambios Frontend

- Pantalla ParámetrosGenerales (recibe programa por ruta o menú). Grilla o formulario: Clave, label, campo valor según tipo.
- Solo edición; sin botones agregar/eliminar. Validaciones por tipo.
- data-testid: parametrosGral.grid, parametrosGral.valor.{clave}

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | DB | Migración: crear PQ_PARAMETROS_GRAL en Company DB | Tabla por empresa | - |
| T2 | Backend | Resolver Company DB según empresa activa | Conexión dinámica | HU-002 |
| T3 | Backend | Endpoints GET/PUT parametros-gral | Filtro por programa, validación tipos | T1, T2 |
| T4 | Backend | Seeds por módulo (ej. PartesProduccion) | Registros iniciales | T1 |
| T5 | Frontend | Pantalla ParámetrosGenerales | Recibe programa, edición valores | HU-001, HU-002 |
| T6 | Frontend | Campos dinámicos según tipo_valor | String, Int, DateTime, Bool, Decimal, Text | T5 |
| T7 | Tests | Integration: GET/PUT parametros, validación tipos | Tests pasan | T3 |
| T8 | Tests | E2E: editar parámetro, guardar, verificar | Playwright | T6 |

---

## Referencias

- docs/00-contexto/05-parametros-generales.md
- docs/modelo-datos/md-empresas/pq-parametros-gral.md
- .cursor/rules/27-parametros-generales-por-modulo.md
- .cursor/rules/28-plan-tareas-hu-parametros-generales.md

---

## Archivos creados/modificados

**Backend:**
- `backend/config/database.php` (conexión company)
- `backend/app/Http/Middleware/SetCompanyConnection.php`
- `backend/database/migrations/2026_02_27_000004_create_pq_parametros_gral_table.php`
- `backend/app/Http/Controllers/Api/V1/ParametrosGralController.php`
- `backend/database/seeders/PqParametrosGralSeeder.php`
- `backend/routes/api.php` (rutas parametros-gral)
- `backend/app/Http/Kernel.php` (middleware company.connection)

**Frontend:**
- `frontend/src/shared/services/parametrosGral.service.ts`
- `frontend/src/features/parametros/pages/ParametrosGeneralesPage.tsx`
- `frontend/src/features/parametros/pages/ParametrosGeneralesPage.css`
- `frontend/src/features/parametros/index.ts`
- `frontend/src/app/App.tsx` (ruta /parametros/:programa)

## Comandos ejecutados

- `php artisan migrate --database=company` (crear PQ_PARAMETROS_GRAL en Company DB)
- `php artisan db:seed --class=PqParametrosGralSeeder` (datos ejemplo PartesProduccion)
- `npm run build` (frontend)

## Notas y decisiones

- Company DB se resuelve por X-Company-Id en SetCompanyConnection. La columna de nombre de BD es `NombreBD` (PQ_Empresa) o `nombre_bd` (pq_empresa); el middleware soporta ambos esquemas.
- Solo edición de valores; no ABM de claves.
- Ruta frontend: /parametros/:programa (ej. /parametros/PartesProduccion).

## Pendientes / follow-ups

- Agregar ítem en pq_menus para invocar desde menú.
- Seeds por módulo según HUs de cada módulo.
- Tests integration/E2E según plan T7-T8.
