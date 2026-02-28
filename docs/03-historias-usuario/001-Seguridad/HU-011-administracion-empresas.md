# HU-011 – Administración de empresas

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero gestionar las empresas registradas en la instalación para asignar bases de datos y configurar el acceso multiempresa, vinculando cada empresa con la base de datos y el identificador correspondiente de Tango.

## Criterios de aceptación

- El administrador puede listar empresas de la tabla `PQ_Empresa`.
- El listado permite filtrar por nombre, habilitada.
- El administrador puede crear una empresa: NombreEmpresa, NombreBD, Habilita, imagen, theme.
- **Al crear una empresa, el usuario debe establecer:**
  - **Nombre de la base de datos** (NombreBD): identificador técnico de la Company DB.
  - **Nombre de la empresa** (NombreEmpresa): nombre visual al usuario.
  - **Vinculación con Tango:** Se valida contra la tabla `EMPRESA` de Tango. Se recomienda usar el mismo código o ID de empresa de Tango para evitar repeticiones y mantener trazabilidad.
- **Validación de asignación única:** El NombreBD (o el código/ID de empresa de Tango si se usa) no debe estar ya asignado a otra empresa en `PQ_Empresa`. Si ya existe, el sistema rechaza la creación y muestra mensaje de error.
- El NombreBD debe ser único (identificador técnico de la base de datos).
- El administrador puede editar una empresa existente.
- El administrador puede habilitar/inhabilitar una empresa (Habilita).
- Una empresa inhabilitada no aparece en el selector de empresas para los usuarios.
- El theme define el tema DevExtreme a aplicar cuando el usuario selecciona esa empresa.
- La creación de la base de datos física (Company DB) puede ser manual o automática según política de infraestructura.

## Tabla involucrada

- `PQ_Empresa`: IDEmpresa, NombreEmpresa, NombreBD, Habilita, imagen, theme
- `EMPRESA` (Tango): tabla de referencia para validar que la base de datos y el identificador no estén ya asignados

## Reglas de negocio

- Solo administradores pueden gestionar empresas.
- El NombreBD se usa para conectar a la Company DB correspondiente.
- Al crear una empresa, validar que el NombreBD (o código/ID de Tango) no esté ya asignado en `PQ_Empresa`.
- Se recomienda usar el código o ID de empresa de Tango como criterio de unicidad para evitar duplicados entre PaqSuite y Tango.

## Dependencias

- HU-001 (Login)
- Tabla `PQ_Empresa` creada
- Acceso de lectura a tabla `EMPRESA` de Tango (para validación)

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema PQ_Empresa
- `docs/00-contexto/00-contexto-global-erp.md` – Bases de datos por empresa
- `.cursor/rules/25-tablas-tango-politica.md` – Política de tablas Tango (solo lectura)
