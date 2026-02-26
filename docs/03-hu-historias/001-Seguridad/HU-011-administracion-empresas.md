# HU-011 – Administración de empresas

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero gestionar las empresas registradas en la instalación para asignar bases de datos y configurar el acceso multiempresa.

## Criterios de aceptación

- El administrador puede listar empresas de la tabla `PQ_Empresa`.
- El listado permite filtrar por nombre, habilitada.
- El administrador puede crear una empresa: NombreEmpresa, NombreBD, Habilita, imagen, theme.
- El NombreBD debe ser único (identificador técnico de la base de datos).
- El administrador puede editar una empresa existente.
- El administrador puede habilitar/inhabilitar una empresa (Habilita).
- Una empresa inhabilitada no aparece en el selector de empresas para los usuarios.
- El theme define el tema DevExtreme a aplicar cuando el usuario selecciona esa empresa.
- La creación de la base de datos física (Company DB) puede ser manual o automática según política de infraestructura.

## Tabla involucrada

- `PQ_Empresa`: IDEmpresa, NombreEmpresa, NombreBD, Habilita, imagen, theme

## Reglas de negocio

- Solo administradores pueden gestionar empresas.
- El NombreBD se usa para conectar a la Company DB correspondiente.

## Dependencias

- HU-001 (Login)
- Tabla `PQ_Empresa` creada

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema PQ_Empresa
- `docs/00-contexto/00-contexto-global-erp.md` – Bases de datos por empresa
