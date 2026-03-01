# TR-001 – Listado de grupos empresarios

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-001 – Listado de grupos empresarios    |
| Épica              | 002 – Grupos empresarios                   |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Administrador                              |
| Dependencias       | HU-001 Login, tablas PQ_GrupoEmpresario    |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                                |

**Origen:** [HU-001 – Listado de grupos empresarios](../../03-historias-usuario/002-GruposEmpresarios/HU-001-listado-grupos-empresarios.md)

---

## 1) HU Refinada

- **Título:** Listado de grupos empresarios
- **Narrativa:** Como administrador quiero listar los grupos empresarios definidos en el sistema para ver las agrupaciones existentes y acceder a su gestión.
- **Contexto:** Grilla con estándar 24 (filtros, agrupación, column chooser, totalizadores, selección). Acciones crear, editar, eliminar.
- **In scope:** Listado con id, descripción, cantidad empresas; filtros; ordenamiento; acciones.
- **Out of scope:** Creación, edición, eliminación (HUs 002, 003, 004).

---

## 2) Criterios de Aceptación

- Listar grupos de PQ_GrupoEmpresario. Columnas: id, descripción, cantidad empresas asignadas.
- Filtrar por descripción. Ordenar por descripción, cantidad empresas.
- Acciones desde listado: crear, editar, eliminar.
- Estándar grillas (regla 24): filtros, agrupación, column chooser, totalizadores, selección.
- Sin grupos: mensaje informativo y botón crear el primero.

### Escenarios Gherkin

```gherkin
Feature: Listado de grupos empresarios

  Scenario: Administrador lista grupos empresarios
    Given el administrador está autenticado
    When accede a Grupos empresarios
    Then ve grilla con columnas id, descripción, cantidad empresas
    And puede filtrar por descripción
    And puede ordenar por descripción o cantidad empresas

  Scenario: Listado vacío
    Given no existen grupos empresarios
    When el administrador accede al listado
    Then ve mensaje informativo
    And ve botón "Crear el primero"

  Scenario: Acciones desde listado
    Given existen grupos en el listado
    When el administrador está en la grilla
    Then ve acciones Crear, Editar, Eliminar disponibles
```

---

## 3) Reglas de Negocio

- Solo administradores. Cantidad empresas por agregación sobre PQ_GrupoEmpresario_Empresas.

---

## 4) Impacto en Datos

- Tablas PQ_GrupoEmpresario (id, descripcion), PQ_GrupoEmpresario_Empresas (id_grupo, id_empresa). Ya definidas en md-diccionario.
- Migración si no existen. PQ_GrupoEmpresario_Empresas: PK compuesta (id_grupo, id_empresa), FKs.

---

## 5) Contratos de API

- GET /api/grupos-empresarios: listado con cantidad empresas (agregación). Query params: filter[descripcion], sort.
- Response: { data: [{ id, descripcion, cantidadEmpresas }] }

---

## 6) Cambios Frontend

- Pantalla listado grupos empresarios (grilla DevExtreme). Columnas id, descripción, cantidadEmpresas. Toolbar: Crear, Editar, Eliminar.
- data-testid: gruposEmpresarios.grid, gruposEmpresarios.create, gruposEmpresarios.edit, gruposEmpresarios.delete

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | DB | Migración PQ_GrupoEmpresario, PQ_GrupoEmpresario_Empresas | Tablas con PKs, FKs | - |
| T2 | Backend | Endpoint GET grupos-empresarios con cantidad empresas | Listado operativo | T1 |
| T3 | Backend | Política solo administradores | 403 si no admin | - |
| T4 | Frontend | Pantalla listado (grilla estándar 24) | Filtros, orden, acciones | HU-001 Login |
| T5 | Tests | Integration: GET listado | Tests pasan | T2 |
| T6 | Tests | E2E: listar grupos, acceder a crear | Playwright | T4 |

---

## Archivos creados/modificados

### Backend
- `backend/database/migrations/2026_02_28_000004_create_pq_grupo_empresario_table.php` (creado)
- `backend/database/migrations/2026_02_28_000005_create_pq_grupo_empresario_empresas_table.php` (creado)
- `backend/app/Models/PqGrupoEmpresario.php` (creado)
- `backend/app/Models/PqEmpresa.php` (creado)
- `backend/app/Http/Controllers/Api/V1/Admin/GrupoEmpresarioController.php` (creado)
- `backend/routes/api.php` (modificado: ruta GET grupos-empresarios)

### Frontend
- `frontend/src/features/admin/pages/GruposEmpresariosAdminPage.tsx` (creado)
- `frontend/src/features/admin/pages/GrupoEmpresarioCrearPage.tsx` (creado, placeholder)
- `frontend/src/features/admin/services/admin.service.ts` (modificado: adminGruposEmpresariosApi)
- `frontend/src/app/App.tsx` (modificado: rutas)
- `frontend/src/app/Sidebar.tsx` (modificado: ítem Grupos empresarios)

### Tests
- `backend/tests/Feature/Api/V1/Admin/GrupoEmpresarioControllerTest.php` (creado)
- `frontend/tests/e2e/grupos-empresarios.spec.ts` (creado)

## Comandos ejecutados

```bash
cd backend && php artisan migrate
cd frontend && npm run build
```

## Notas y decisiones

- Proyecto usa SQL Server (MSSQL). Config por defecto: `DB_CONNECTION=sqlsrv`.
- Inserciones con `DB::table()` usan `DB::raw('GETDATE()')` para timestamps (regla 20-mssql).

## Pendientes / follow-ups

- Ninguno
