# HU-005 – Detalle de grupo empresario

## Épica
002 – Grupos Empresarios

## Clasificación
SHOULD-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero ver el detalle de un grupo empresario para conocer su descripción y el listado de empresas que lo componen sin entrar en modo edición.

## Criterios de aceptación

- El administrador puede acceder al detalle desde el listado (clic en fila o botón "Ver").
- Se muestra la descripción del grupo.
- Se muestra el listado de empresas asignadas (nombre, código o identificador).
- El listado de empresas es de solo lectura en esta vista.
- Existen botones o enlaces para editar o eliminar el grupo.
- Si el grupo no existe o fue eliminado, se muestra mensaje apropiado y se redirige al listado.

## Tablas involucradas

- `PQ_GrupoEmpresario`: id, descripcion
- `PQ_GrupoEmpresario_Empresas`: id_grupo, id_empresa
- `PQ_Empresa`: para mostrar nombre de cada empresa

## Reglas de negocio

- Solo administradores pueden ver el detalle.
- La información es de solo lectura; para modificar se usa la pantalla de edición.

## Dependencias

- HU-001 (Listado) de esta épica

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema
