# Regla: Plan de tareas del HU-007 (Parámetros generales)

## Objetivo

El HU-007 define el **proceso general** de mantenimiento de `PQ_PARAMETROS_GRAL`. Este proceso se implementa **una sola vez** y es invocado por cada módulo que tenga parámetros (cada módulo define sus claves en su propia HU; el menú filtra por `PQ_MENUS.Procedimiento` = nombre clave del módulo).

## Alcance del plan de tareas

Al descomponer HU-007 en tareas (TR), seguir la estructura de `.cursor/rules/13-user-story-to-task-breakdown.md` (tabla de metadatos, escenarios Gherkin, etc.) y además incluir:

### 1. Base de datos

- Migración para crear `PQ_PARAMETROS_GRAL` (o ejecutar el script del diseño).
- Seed inicial vacío o con parámetros de ejemplo según módulos existentes.
- Referencia: `docs/modelo-datos/md-empresas/pq-parametros-gral.md`

### 2. Backend

- Endpoint `GET /api/v1/parametros-gral?programa={programa}` – listar parámetros del módulo. La consulta se ejecuta contra la BD de la empresa activa.
- Endpoint `PATCH /api/v1/parametros-gral` – actualizar solo los campos `Valor_*` (no crear ni eliminar).
- Validación: el usuario debe tener permiso para la empresa activa.
- El proceso **no permite** agregar ni eliminar registros.

### 3. Frontend

- Pantalla o popup de edición de parámetros.
- Filtro implícito por `programa` (recibido por ruta o por `PQ_MENUS.Procedimiento` del ítem de menú).
- Solo campos editables: los `Valor_*` según `tipo_valor` de cada fila.
- Labels legibles para cada clave (mapeo clave → etiqueta i18n).

### 4. Integración con menú

- Cada ítem de menú que abre este proceso debe tener `procedimiento` = nombre clave del módulo (ej. `PartesProduccion`).
- El frontend recibe el `procedimiento` y lo envía al backend como `programa` para filtrar.

### 5. Deploy / seed

- Incluir en el proceso de deploy la carga de registros de `PQ_PARAMETROS_GRAL` en cada Company DB (una BD por empresa).
- Los seeds por módulo definen las filas (Programa, Clave, tipo_valor) con valores por defecto; el usuario solo edita los `Valor_*`.

## Nombre clave del módulo (PROGRAMA)

En el TR del HU-007, aclarar que el **nombre clave** (campo PROGRAMA) lo define cada módulo en su HU de parámetros. El proceso general es agnóstico; filtra por el valor recibido.

## Referencias

- `docs/03-historias-usuario/000-Generalidades/HU-007-Parametros-generales.md`
- `docs/00-contexto/05-parametros-generales.md`
- `docs/modelo-datos/md-empresas/pq-parametros-gral.md`
