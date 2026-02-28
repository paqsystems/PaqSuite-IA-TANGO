# HU-004 – Eliminación de grupo empresario

## Épica
002 – Grupos Empresarios

## Clasificación
MUST-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero eliminar un grupo empresario cuando ya no se utilice para liberar el diccionario y evitar configuraciones obsoletas.

## Criterios de aceptación

- El administrador puede solicitar la eliminación de un grupo desde el listado o detalle.
- Se muestra confirmación antes de eliminar ("¿Está seguro de eliminar el grupo X?").
- Al confirmar, se eliminan primero los registros de `PQ_GrupoEmpresario_Empresas` y luego el de `PQ_GrupoEmpresario`.
- Si el grupo está referenciado en parámetros de módulos (ej. "GrupoEmpresario" en Acopios) u otros procesos, se valida y se informa al usuario antes de permitir la eliminación.
- Si hay dependencias que impiden la eliminación, se muestra mensaje claro indicando dónde se usa.
- Tras eliminar correctamente, se redirige al listado con mensaje de éxito.

## Tablas involucradas

- `PQ_GrupoEmpresario`
- `PQ_GrupoEmpresario_Empresas`
- Posibles referencias: `PQ_Parametros_Gral` o tablas de configuración de módulos que usen el grupo

## Reglas de negocio

- Solo administradores pueden eliminar grupos.
- Eliminación en cascada: primero `PQ_GrupoEmpresario_Empresas`, luego `PQ_GrupoEmpresario`.
- Si el grupo está en uso (parámetros, reportes), se puede bloquear la eliminación o advertir fuertemente.

## Dependencias

- HU-001 (Listado), HU-002 (Creación), HU-003 (Edición) de esta épica

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema
- `docs-paqsystems/acopios/contexto-acopios.md` – Uso de GrupoEmpresario en parámetros
