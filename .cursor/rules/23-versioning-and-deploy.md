# 23 — Versionado y Actualización de Base de Datos en Deploy

## 0) Propósito

Esta regla define la metodología de versionado de la aplicación y la actualización de bases de datos (esquema + datos fijos) en cada deploy. El proyecto opera en modelo **multiempresa** (Dictionary DB + Company DB por empresa).

---

## 1) Versión de Aplicación (SemVer estricto)

### Formato

**MAJOR.MINOR.PATCH** (ej: `1.2.3`)

| Componente | Cuándo incrementar | Ejemplo |
|------------|--------------------|---------|
| **MAJOR** | Breaking changes (columnas eliminadas, contrato API incompatible, cambios estructurales que requieren migración manual) | 1.0.0 → 2.0.0 |
| **MINOR** | Nuevas funcionalidades compatibles (columnas nuevas, tablas nuevas, endpoints nuevos) | 1.0.0 → 1.1.0 |
| **PATCH** | Correcciones de bugs, ajustes menores sin cambios de BD | 1.0.0 → 1.0.1 |

### Fuente de verdad

- **Archivo único:** `VERSION` en la raíz del proyecto.
- Contenido: una sola línea con la versión (ej: `1.0.0`).
- No duplicar la versión en `package.json` ni `composer.json` como fuente principal; pueden leer de `VERSION` si se requiere en build.

### Actualización

- Actualizar `VERSION` en el commit/merge que introduce el cambio.
- Para releases: crear tag Git `v{MAJOR.MINOR.PATCH}` (ej: `v1.2.3`).

### Responsabilidad

- **El equipo (humano) es responsable del bump de versión.** El agente IA no lo ejecuta automáticamente en cada deploy.
- El agente puede sugerir y aplicar el bump cuando implementa cambios, pero la decisión final corresponde al usuario.

---

## 2) Esquema de Base de Datos (Migrations)

### Convenciones (alineadas con regla 09)

- Una migration por cada cambio de estructura.
- Migrations reversibles (`down()`), aunque **no se ejecuta rollback en producción**.
- No modificar migrations ya aplicadas en producción.
- `migrate:fresh` solo en desarrollo.

### Política de rollback

- **No hay política de rollback de migrations en producción.**
- Si una migration falla o introduce un error, se corrige con una nueva migration (forward-only).

---

## 3) Datos Fijos (Catálogos, Menús, Config)

### Metodología documentada

Para catálogos versionados (menús, tipos, etc.) se sigue la metodología de `docs/03-hu-historias/Historia_PQ_MENUS_seed.md`:

- **Fuente de verdad:** archivo versionado en repo (ej: `PQ_MENUS.seed.json`).
- **Aplicación:** script idempotente (MERGE/upsert) o seeder Laravel con `updateOrCreate` / `firstOrCreate`.
- **Idempotencia obligatoria:** ejecutar 2 veces no debe duplicar ni corromper datos.

### Seeders Laravel

- Usar seeders para catálogos base (tipos de cliente, tipos de tarea, usuarios admin, etc.).
- **Obligatorio:** seeders deben ser idempotentes cuando se ejecutan en deploy.
- Ver `backend/database/seeders/` y regla 09 (Seeders y Factories).

---

## 4) Deploy: Migrations y Seeds

### Orden de ejecución

En cada deploy:

1. **Migrations** (esquema)
2. **Seeds** (datos fijos / catálogos)

### Seeds siempre en deploy

- Los seeds se ejecutan **en cada deploy**, no solo en instalación nueva.
- Por eso es obligatorio que sean idempotentes.

### Instalación nueva

- Una sola base de datos (empresa ejemplo).
- Flujo: `migrate` → `db:seed`.

---

## 5) Multiempresa: Múltiples Bases de Datos

### Modelo

- **Dictionary DB:** usuarios, empresas, roles, permisos, asignaciones (configuración central).
- **Company DB:** una por empresa; datos operativos (clientes, tareas, etc.).
- Cada empresa tiene su propia base (campo `NombreBD` en `PQ_Empresa` o equivalente).

### Deploy multiempresa

En cada deploy deben actualizarse:

1. **Dictionary DB:** `migrate` → `db:seed`
2. **Cada Company DB:** `migrate` → `db:seed` (iterando sobre las empresas registradas)

### Factibilidad en deploy

- **Es factible** automatizarlo en el pipeline de deploy:
  - Conectar a Dictionary DB.
  - Obtener lista de bases de empresa (ej: `SELECT NombreBD FROM PQ_Empresa WHERE Habilita = 1`).
  - Para cada `NombreBD`: configurar conexión temporal y ejecutar `php artisan migrate --force` y `php artisan db:seed --force`.
- Requiere un **comando o script** (ej: `php artisan deploy:update-all-databases`) que encapsule esta lógica.
- El script debe manejar errores por empresa (log, continuar con las demás, o fallar según política).

### Documentación futura

- Cuando se implemente el comando multiempresa, documentarlo en `docs/deploy-ci-cd.md` y en el pipeline de CD.

---

## 6) Workflow: Push, PR o CI

Cuando el usuario solicite **push**, **PR** o **aplicar CI**:

1. **Revisar:** El agente debe revisar los cambios realizados (commit(s), diff, etc.).
2. **Sugerir:** Proponer un bump de versión según los criterios de la sección 1:
   - Si hay breaking changes → MAJOR
   - Si hay nuevas funcionalidades compatibles → MINOR
   - Si hay correcciones de bugs o ajustes menores → PATCH
   - Si no hay cambios relevantes → sin bump (opcional: indicarlo explícitamente)
3. **Mostrar propuesta:** Indicar el valor actual (ej: `1.0.0`) y el propuesto (ej: `1.1.0`), con breve justificación.
4. **Confirmar:** Esperar confirmación del usuario antes de actualizar `VERSION` y proceder con push/PR.
5. Si el usuario rechaza o no confirma, no aplicar el bump y continuar sin modificar `VERSION`.

---

## 7) Resumen de Criterios

| Criterio            | Decisión                                                    |
|---------------------|-------------------------------------------------------------|
| Versión             | SemVer estricto (MAJOR.MINOR.PATCH)                         |
| Fuente de versión   | Archivo `VERSION` en raíz                                   |
| Seeds en deploy     | Siempre (obligatorio idempotencia)                          |
| Multiempresa        | Migrate + seed en Dictionary DB y en cada Company DB        |
| Instalación nueva   | Una sola BD (empresa ejemplo)                               |
| Datos fijos         | Metodología PQ_MENUS (JSON + idempotente) o seeders Laravel |
| Rollback migrations | No; solo forward con nuevas migrations                      |

---

## 8) Referencias

- **Migrations:** `.cursor/rules/09-data-access-orm-sql.md`
- **Deploy:** `docs/deploy-ci-cd.md`
- **Menú versionado:** `docs/03-hu-historias/Historia_PQ_MENUS_seed.md`
- **Arquitectura multiempresa:** `docs/01-arquitectura/01-arquitectura-proyecto.md`, `docs/00-contexto/00-contexto-global-erp.md`
