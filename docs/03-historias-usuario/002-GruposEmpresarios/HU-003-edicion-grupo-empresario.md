# HU-003 – Edición de grupo empresario

## Épica
002 – Grupos Empresarios

## Clasificación
MUST-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero editar un grupo empresario existente para modificar su descripción o las empresas que lo componen.

## Criterios de aceptación

- El administrador puede editar la descripción del grupo.
- El administrador puede agregar empresas al grupo (desde las disponibles).
- El administrador puede quitar empresas del grupo.
- El grupo debe conservar al menos una empresa tras la edición.
- Si se intenta quitar la última empresa, se muestra error de validación.
- No se permiten empresas duplicadas.
- Tras guardar, se actualizan `PQ_GrupoEmpresario` y `PQ_GrupoEmpresario_Empresas`.
- Se muestra mensaje de éxito y se actualiza la vista (listado o detalle).

## Tablas involucradas

- `PQ_GrupoEmpresario`: id, descripcion
- `PQ_GrupoEmpresario_Empresas`: id_grupo, id_empresa

## Reglas de negocio

- Un grupo debe tener al menos una empresa en todo momento.
- Solo administradores pueden editar grupos.

## Dependencias

- HU-001 (Listado), HU-002 (Creación) de esta épica
- HU-011 (Empresas) de épica 001

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema
