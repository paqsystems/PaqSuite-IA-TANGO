# TR-014 – Administración de atributos de rol

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-014 – Administración de atributos de rol |
| Épica              | 001 – Seguridad                            |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Administrador                              |
| Dependencias       | HU-001 (Login), HU-012 (Roles)             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                                |

**Origen:** [HU-014 – Administración de atributos de rol](../../03-historias-usuario/001-Seguridad/HU-014-administracion-atributos-rol.md)

---

## 1) HU Refinada

- **Título:** Administración de atributos de rol
- **Narrativa:** Como administrador quiero definir los permisos granulares de cada rol por opción de menú (Alta, Baja, Modificación, Reporte).
- **Contexto:** CRUD PQ_RolAtributo. Vincula rol + opción menú + permisos (Alta, Baja, Modi, Repo). Si AccesoTotal=true no requiere atributos.
- **In scope:** Listar atributos de un rol, asignar/editar/eliminar permisos por opción de menú.
- **Out of scope:** Roles con AccesoTotal (acceso implícito).

---

## 2) Criterios de Aceptación

- Listar atributos de un rol (PQ_RolAtributo). Cada atributo: rol + opción menú + Permiso_Alta, Permiso_Baja, Permiso_Modi, Permiso_Repo.
- Asignar permisos a rol por cada opción de pq_menus.
- Si AccesoTotal=true, no se requieren atributos. Combinación (IDRol, IDOpcionMenu, IDAtributo) única.
- Editar o eliminar atributos de rol.

### Escenarios Gherkin

```gherkin
Feature: Administración de atributos de rol

  Scenario: Administrador lista atributos de un rol
    Given el administrador está autenticado
    And existe un rol sin AccesoTotal
    When accede a atributos de ese rol
    Then ve listado de opciones de menú con permisos (Alta, Baja, Modi, Repo)

  Scenario: Administrador asigna permisos a rol por opción menú
    Given el administrador está en atributos del rol
    When selecciona opción de menú "Usuarios"
    And marca Permiso_Alta y Permiso_Modi
    And hace clic en Guardar
    Then se crea/actualiza registro en PQ_RolAtributo
    And la combinación (rol, opción, atributo) es única

  Scenario: Rol con AccesoTotal no requiere atributos
    Given existe un rol con AccesoTotal=true
    When el administrador accede a atributos de ese rol
    Then no se requieren atributos granulares
    And el rol tiene acceso implícito a todo
```

---

## 3) Reglas de Negocio

- Solo administradores. Opciones de menú de pq_menus. IDAtributo según diseño (fijo o catálogo).

---

## 4) Impacto en Datos

- Tabla PQ_RolAtributo (ya existe). Relación con Pq_Rol, pq_menus.

---

## 5) Contratos de API

- GET /api/roles/{id}/atributos: listado atributos del rol. POST /api/roles/{id}/atributos. PUT /api/roles/{id}/atributos/{atributoId}. DELETE.
- Validación: combinación única; rol sin AccesoTotal para requerir atributos.

---

## 6) Cambios Frontend

- Pantalla atributos de rol (por rol seleccionado). Grilla o formulario por opción de menú con checkboxes Alta, Baja, Modi, Repo.
- data-testid: rolAtributos.grid, rolAtributos.edit

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | CRUD atributos de rol (controller) | Endpoints operativos | - |
| T2 | Backend | Validación combinación única | 422 si duplicado | - |
| T3 | Frontend | Pantalla atributos de rol | Listado por rol | HU-001, HU-012 |
| T4 | Frontend | Formulario asignar permisos por opción menú | CRUD completo | T3 |
| T5 | Tests | Integration: CRUD atributos | Tests pasan | T1, T2 |
| T6 | Tests | E2E: asignar permisos a rol | Playwright | T4 |

---

## Archivos creados/modificados

### Backend
- `backend/database/migrations/2026_02_28_000006_create_pq_rol_atributo_table.php` (creado)
- `backend/app/Http/Controllers/Api/V1/Admin/RolAtributoController.php` (creado)
- `backend/routes/api.php` (rutas GET/PUT roles/{id}/atributos)

### Frontend
- `frontend/src/features/admin/pages/RolAtributosPage.tsx` (creado)
- `frontend/src/features/admin/services/admin.service.ts` (getAtributos, updateAtributos)
- `frontend/src/features/admin/pages/RolesAdminPage.tsx` (columna Acciones: botón Atributos)
- `frontend/src/app/App.tsx` (ruta /admin/roles/:id/atributos)

## Comandos ejecutados

```bash
cd backend && php artisan migrate
cd frontend && npm run build
```

## Notas y decisiones

- Diseño simplificado: una fila por (id_rol, id_opcion_menu) con columnas permiso_alta, permiso_baja, permiso_modi, permiso_repo.
- Roles con acceso_total: mensaje informativo, no se permiten atributos.
- Solo opciones de pq_menus con procedimiento no vacío.

## Pendientes / follow-ups

- Ninguno
