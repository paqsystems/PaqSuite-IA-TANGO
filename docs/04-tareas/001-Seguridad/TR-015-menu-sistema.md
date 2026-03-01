# TR-015 – Menú del sistema (seed versionado)

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-015 – Menú del sistema                  |
| Épica              | 001 – Seguridad                            |
| Prioridad          | MUST-HAVE                                  |
| Roles              | Administrador / DevOps                     |
| Dependencias       | -                                          |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-015 – Menú del sistema](../../03-historias-usuario/001-Seguridad/HU-015-menu-sistema.md)

---

## 1) HU Refinada

- **Título:** Menú del sistema (seed versionado)
- **Narrativa:** Como administrador quiero que el menú del sistema esté versionado y sea reproducible en todos los entornos.
- **Contexto:** Fuente de verdad en repo (seed JSON). Seeder idempotente. Integración en deploy/migraciones.
- **In scope:** Archivo seed versionado, script idempotente, integración en deploy. Fuera: ABM UI de menú.
- **Out of scope:** ABM completo de edición de menú en UI.

---

## 2) Criterios de Aceptación

- Archivo seed versionado (PQ_MENUS.seed.json o equivalente) en repo.
- Seeder idempotente que sincroniza pq_menus (inserta faltantes, actualiza, no duplica).
- Ejecutar 2 veces no duplica registros. Orden determinístico por parent.
- Constraints UNIQUE(parent, order). Integración en flujo deploy/migraciones.

### Escenarios Gherkin

```gherkin
Feature: Menú del sistema (seed versionado)

  Scenario: Seeder idempotente - ejecutar dos veces
    Given existe el archivo seed PQ_MENUS en repo
    When se ejecuta PqMenuSeeder por primera vez
    Then se insertan los registros en pq_menus
    When se ejecuta PqMenuSeeder por segunda vez
    Then no se duplican registros
    And los existentes se actualizan si cambió el seed

  Scenario: Orden determinístico por parent
    Given el seed tiene estructura jerárquica (parent, order)
    When se ejecuta el seeder
    Then los ítems se insertan en orden correcto
    And cada parent existe antes de sus hijos

  Scenario: Actualización de menú en deploy
    Given se modificó el archivo seed en repo
    When se ejecuta el flujo de deploy/migraciones
    Then PqMenuSeeder se ejecuta
    And pq_menus queda sincronizado con la fuente de verdad
```

---

## 3) Reglas de Negocio

- Fuente de verdad en repo. IDs estables para referenciar permisos/roles.

---

## 4) Impacto en Datos

- Tabla pq_menus (ya existe). Migración si falta constraint UNIQUE(parent, order).
- Seed: PqMenuSeeder (Laravel) o script SQL idempotente.

---

## 5) Contratos de API

- GET /api/menus (o equivalente): menú filtrado por permisos del usuario. Ya puede existir.
- Este TR se centra en el seed, no en endpoints nuevos.

---

## 6) Cambios Backend / DevOps

- Archivo seed (JSON o PHP array). PqMenuSeeder idempotente (upsert).
- Integración en DatabaseSeeder. Documentar proceso de actualización.

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | DB | Migración: constraint UNIQUE(parent, order) si no existe | Constraint aplicado | - |
| T2 | Backend | Archivo seed PQ_MENUS (JSON o array PHP) | Fuente de verdad en repo | - |
| T3 | Backend | PqMenuSeeder idempotente (upsert/merge) | Ejecutar 2 veces sin duplicar | T2 |
| T4 | Backend | Integrar PqMenuSeeder en DatabaseSeeder | Seed en flujo deploy | T3 |
| T5 | Docs | Documentar actualización de menú (cambio seed + ejecución) | README o docs | T4 |
| T6 | Tests | Test idempotencia: ejecutar seed 2 veces | Sin duplicados | T3 |
| T7 | Tests | Validación jerárquica: parents existentes | Test pasa | T3 |

---

## Referencias

- docs/03-historias-usuario/Historia_PQ_MENUS_seed.md
- docs/modelo-datos/md-diccionario/md-diccionario.md – Esquema pq_menus

---

## Archivos creados/modificados

**Backend:** PqMenuSeeder idempotente, PQ_MENUS.seed.v2.json en docs/backend/seed/PQ_MENUS, DatabaseSeeder.

## Notas y decisiones

- Fuente: docs/backend/seed/PQ_MENUS/PQ_MENUS.seed.v2.json. Path: dirname(base_path())/docs/...
