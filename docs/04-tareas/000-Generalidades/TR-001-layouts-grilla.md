# TR-001 – Layouts persistentes de grillas

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-001 – Layouts persistentes de grillas   |
| Épica              | 000 – Generalidades                        |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Usuario que opera con grillas              |
| Dependencias       | Tabla pq_grid_layouts, estándar grillas    |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-001 – Layouts persistentes de grillas](../../03-historias-usuario/000-Generalidades/HU-001-layouts-grilla.md)

---

## 1) HU Refinada

- **Título:** Layouts persistentes de grillas
- **Narrativa:** Como usuario que trabajo frecuentemente con grillas quiero guardar y recuperar formatos personalizados (columnas, filtros, agrupaciones, totalizadores) para no tener que reconfigurar la vista cada vez que accedo a la pantalla.
- **Contexto:** Las grillas DevExtreme deben permitir persistir y cargar configuraciones de vista por usuario. Los layouts son compartidos (todos pueden usar cualquier layout) pero solo el creador puede modificar/eliminar.
- **Suposiciones:** Tabla `pq_grid_layouts` definida en modelo; estándar de grillas (24-devextreme-grid-standards.md) aplicado.
- **In scope:** CRUD de layouts, guardar/cargar/eliminar, identificación por proceso+grid_id.
- **Out of scope:** Layouts por empresa; sincronización multi-dispositivo en tiempo real.

---

## 2) Criterios de Aceptación

- El usuario puede guardar el formato actual de la grilla con un nombre (columnas, orden, filtros, agrupaciones, ordenamiento, totalizadores).
- "Guardar" sobre layout seleccionado actualiza; "Guardar como..." crea nuevo; "Guardar" sobre plantilla original actúa como "Guardar como...".
- Los layouts son compartidos: todos los usuarios pueden ver y usar cualquier layout.
- Al abrir una pantalla con grilla, se presenta el formato utilizado la última vez por el usuario (si existe).
- El usuario puede elegir entre varios layouts disponibles para esa grilla.
- Solo el creador puede eliminar un layout; layouts de otros no muestran opción eliminar (o deshabilitada).
- Cada grilla se identifica por `proceso` (pq_menus.procedimiento) y `grid_id` (ej. "default", "master", "detalle").

### Escenarios Gherkin

```gherkin
Feature: Layouts persistentes de grillas

  Scenario: Usuario guarda layout con nombre
    Given el usuario está autenticado
    And está en una pantalla con grilla (ej. Usuarios)
    And ha configurado columnas, filtros u ordenamiento
    When hace clic en "Guardar como..."
    And ingresa el nombre "Mi vista"
    Then se crea un nuevo layout con ese nombre
    And el layout queda disponible en el selector

  Scenario: Usuario carga layout guardado previamente
    Given el usuario está autenticado
    And existe un layout "Mi vista" para la grilla actual
    When selecciona "Mi vista" en el selector de layouts
    Then la grilla aplica la configuración de ese layout
    And se registra como último usado

  Scenario: Al abrir pantalla se presenta último layout usado
    Given el usuario está autenticado
    And usó previamente el layout "Mi vista" en esta grilla
    When accede a la pantalla con la grilla
    Then se carga automáticamente el layout "Mi vista"
    And la grilla muestra la configuración guardada

  Scenario: Solo el creador puede eliminar su layout
    Given el usuario está autenticado
    And el layout "Mi vista" fue creado por él
    When selecciona ese layout
    Then ve la opción "Eliminar" habilitada
    When otro usuario selecciona ese layout
    Then no ve la opción "Eliminar" o está deshabilitada

  Scenario: Guardar actualiza layout seleccionado
    Given el usuario está autenticado
    And tiene seleccionado su layout "Mi vista"
    And modifica la configuración de la grilla
    When hace clic en "Guardar"
    Then el layout "Mi vista" se actualiza con la nueva configuración
```

---

## 3) Reglas de Negocio

1. Solo el creador (user_id) puede modificar o eliminar un layout.
2. Todos los usuarios pueden usar cualquier layout.
3. El "último usado" por usuario se determina por registro de uso (tabla auxiliar o campo en preferencias).
4. Layouts filtrados por proceso + grid_id.

---

## 4) Impacto en Datos

### Tabla afectada

- **`pq_grid_layouts`** (Dictionary DB): ya definida en `md-diccionario.md`. Campos: id, user_id, proceso, grid_id, layout_name, layout_data (JSON), is_default, created_at, updated_at.

### Migración

Crear migración Laravel si no existe:

```php
Schema::create('pq_grid_layouts', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->string('proceso', 150);
    $table->string('grid_id', 50)->default('default');
    $table->string('layout_name', 100);
    $table->json('layout_data')->nullable();
    $table->boolean('is_default')->default(false);
    $table->timestamps();
    $table->foreign('user_id')->references('id')->on('users');
});
$table->index(['proceso', 'grid_id', 'layout_name']);
```

### Rollback

```php
Schema::dropIfExists('pq_grid_layouts');
```

### Seed

No se requiere seed; los layouts se crean dinámicamente por los usuarios.

---

## 5) Contratos de API

### GET /api/grid-layouts?proceso={proceso}&gridId={gridId}

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "userId": 1,
      "proceso": "Clientes",
      "gridId": "default",
      "layoutName": "Mi vista",
      "layoutData": { "columns": [...], "filters": [...], "grouping": [...] },
      "isDefault": false,
      "createdAt": "2025-02-27T10:00:00",
      "updatedAt": "2025-02-27T10:00:00",
      "isOwner": true
    }
  ]
}
```

**Autorización:** Usuario autenticado.

### POST /api/grid-layouts

**Request:**
```json
{
  "proceso": "Clientes",
  "gridId": "default",
  "layoutName": "Mi vista",
  "layoutData": { "columns": [...], "filters": [...], "grouping": [...], "sorting": [...], "summary": [...] },
  "isDefault": false
}
```

**Response (201):** Objeto layout creado.

**Errores:** 400 (validación), 401, 422 (layout_name duplicado para mismo proceso+grid_id+user).

### PUT /api/grid-layouts/{id}

**Request:** Mismo body que POST. Solo el creador puede actualizar.

**Response (200):** Objeto layout actualizado.

**Errores:** 401, 403 (no es el creador), 404.

### DELETE /api/grid-layouts/{id}

Solo el creador puede eliminar.

**Response (204):** Sin contenido.

**Errores:** 401, 403, 404.

### GET /api/grid-layouts/last-used?proceso={proceso}&gridId={gridId}

Devuelve el layout usado por última vez por el usuario para esa grilla (si existe).

**Response (200):** Objeto layout o 404 si no hay último usado.

---

## 6) Cambios Frontend

### Componentes

- **GridLayoutManager:** Hook o componente que gestiona guardar/cargar layouts.
- **LayoutSelector:** Dropdown o menú para elegir layout y acciones (Guardar, Guardar como..., Eliminar).
- **DataGridDX / wrapper:** Integrar LayoutSelector en toolbar; pasar proceso y grid_id; aplicar layout_data al DataGrid.

### Estados UI

- Loading al cargar layouts.
- Empty: sin layouts, mostrar opción "Guardar como...".
- Error: mensaje si falla guardar/cargar.

### data-testid

- `grid.{proceso}.{grid_id}.layoutSelector`
- `grid.{proceso}.{grid_id}.layoutSelector.save`
- `grid.{proceso}.{grid_id}.layoutSelector.saveAs`
- `grid.{proceso}.{grid_id}.layoutSelector.delete`
- `grid.{proceso}.{grid_id}.layoutSelector.option.{layoutName}`

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | DB | Migración: crear tabla `pq_grid_layouts` | Tabla creada, FK a users, índice | - |
| T2 | Backend | Modelo `PqGridLayout` con relaciones y scopes | Modelo listo, validaciones | T1 |
| T3 | Backend | Controller `GridLayoutController` con CRUD | Endpoints GET, POST, PUT, DELETE | T2 |
| T4 | Backend | Endpoint GET last-used | Endpoint operativo | T2 |
| T5 | Backend | Validación: solo creador puede PUT/DELETE | 403 si no es owner | T3 |
| T6 | Backend | OpenAPI/Swagger para endpoints de layouts | Documentación actualizada | T3 |
| T7 | Frontend | Hook `useGridLayout(proceso, gridId)` | Carga layouts, aplica al DataGrid | - |
| T8 | Frontend | Componente `LayoutSelector` (dropdown, Guardar, Guardar como, Eliminar) | UI integrada en toolbar | T7 |
| T9 | Frontend | Integrar LayoutSelector en DataGrid wrapper según estándar | Cada grilla con proceso+grid_id tiene selector | T8 |
| T10 | Frontend | Lógica "último usado" (guardar en backend o tabla auxiliar) | Al seleccionar layout, registrar uso | T4, T8 |
| T11 | Tests | Unit: modelo PqGridLayout, validaciones | Tests pasan | T2 |
| T12 | Tests | Integration: CRUD layouts, permisos (solo owner elimina) | Tests pasan | T3, T5 |
| T13 | Tests | E2E: guardar layout, recargar, verificar que se aplica | Playwright cubre flujo | T9 |
| T14 | Docs | Actualizar 24-devextreme-grid-standards con implementación layouts | Regla incluye layouts como obligatorio | T9 |

---

## 8) Estrategia de Tests

### Unit

- Modelo: validación de proceso, grid_id, layout_name.
- Scopes: por proceso+grid_id, por user_id.

### Integration

- POST layout: 201 con datos válidos.
- PUT layout por otro usuario: 403.
- DELETE layout por otro usuario: 403.
- GET listado: solo layouts del proceso+grid_id.

### E2E (Playwright)

- Login → ir a pantalla con grilla → configurar columnas/filtros → Guardar como "Mi vista" → recargar página → verificar que se aplica "Mi vista".

---

## Archivos creados/modificados

**Backend:**
- `backend/database/migrations/2026_02_27_000002_create_pq_grid_layouts_table.php`
- `backend/database/migrations/2026_02_27_000003_create_pq_grid_layout_last_used_table.php`
- `backend/app/Models/PqGridLayout.php`
- `backend/app/Http/Controllers/Api/V1/GridLayoutController.php`
- `backend/routes/api.php` (rutas grid-layouts)

**Frontend:**
- `frontend/src/shared/services/gridLayout.service.ts`
- `frontend/src/shared/hooks/useGridLayout.ts`
- `frontend/src/shared/components/LayoutSelector.tsx`
- `frontend/src/shared/ui/DataGridDX/DataGridDX.tsx` (layoutLoad, gridRef, stateStoring)
- `frontend/src/features/admin/pages/UsersAdminPage.tsx` (integración LayoutSelector)

## Comandos ejecutados

- `php artisan migrate` (crear pq_grid_layouts, pq_grid_layout_last_used)
- `npm run build` (frontend)

## Notas y decisiones

- Se usa tabla auxiliar `pq_grid_layout_last_used` para "último usado" por usuario/proceso/grid.
- Endpoint POST `/grid-layouts/{id}/use` para registrar uso.
- DataGridDX usa stateStoring customLoad para cargar last-used al iniciar.

## Pendientes / follow-ups

- Tests unit/integration/E2E según plan T11-T13.
- Integrar LayoutSelector en otras grillas (Empresas, Roles, Permisos).
