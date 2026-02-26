# HU-001 – Listado de grupos empresarios

## Épica
002 – Grupos Empresarios

## Clasificación
MUST-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero listar los grupos empresarios definidos en el sistema para ver las agrupaciones existentes y acceder a su gestión.

## Criterios de aceptación

- El administrador puede listar grupos de la tabla `PQ_GrupoEmpresario`.
- El listado muestra: id, descripción, cantidad de empresas asignadas.
- El listado permite filtrar por descripción.
- El listado permite ordenar por descripción, cantidad de empresas.
- El administrador puede acceder a crear, editar o eliminar desde el listado.
- Se aplica el estándar de grillas (regla 24): filtros, agrupación, column chooser, totalizadores, selección.
- Si no hay grupos, se muestra mensaje informativo y botón para crear el primero.

## Tablas involucradas

- `PQ_GrupoEmpresario`: id, descripcion
- `PQ_GrupoEmpresario_Empresas`: id_grupo, id_empresa (para contar empresas por grupo)

## Reglas de negocio

- Solo administradores pueden acceder al listado.
- La cantidad de empresas se obtiene por agregación sobre `PQ_GrupoEmpresario_Empresas`.

## Dependencias

- HU-001 (Login) de épica 001
- Tablas `PQ_GrupoEmpresario`, `PQ_GrupoEmpresario_Empresas` creadas

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema
- `.cursor/rules/24-devextreme-grid-standards.md` – Estándar de grillas
