# HU-002 – Creación de grupo empresario

## Épica
002 – Grupos Empresarios

## Clasificación
MUST-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero crear un nuevo grupo empresario asignándole empresas para poder usarlo en reportes consolidados y procesos multi-empresa.

## Criterios de aceptación

- El administrador puede crear un grupo con descripción.
- El administrador debe asignar al menos una empresa al crear el grupo.
- La descripción es obligatoria y no puede estar vacía.
- Se muestra selector/listado de empresas disponibles (`PQ_Empresa` habilitadas).
- El administrador puede seleccionar múltiples empresas para el grupo.
- No se permiten empresas duplicadas en el mismo grupo.
- Tras crear correctamente, se inserta en `PQ_GrupoEmpresario` y en `PQ_GrupoEmpresario_Empresas` las asociaciones.
- Se redirige al listado o al detalle del grupo creado con mensaje de éxito.
- En caso de error (ej. descripción vacía, sin empresas), se muestran mensajes de validación.

## Tablas involucradas

- `PQ_GrupoEmpresario`: id, descripcion
- `PQ_GrupoEmpresario_Empresas`: id_grupo, id_empresa
- `PQ_Empresa`: empresas disponibles (Habilita = 1)

## Reglas de negocio

- Un grupo debe tener al menos una empresa.
- Solo se pueden asignar empresas existentes y habilitadas.
- La descripción puede repetirse (no hay UNIQUE); se recomienda que sea descriptiva.

## Dependencias

- HU-001 (Listado) de esta épica
- HU-011 (Administración de empresas) de épica 001 – deben existir empresas para asignar

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema
