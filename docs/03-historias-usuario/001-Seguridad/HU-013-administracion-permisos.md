# HU-013 – Administración de permisos (asignaciones)  

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Administrador del sistema

## Narrativa

Como administrador quiero asignar usuarios a empresas con roles específicos para controlar qué puede hacer cada usuario en cada empresa.

## Criterios de aceptación

- El administrador puede listar las asignaciones de la tabla `Pq_Permiso`.
- Cada asignación vincula: usuario + empresa + rol.
- El listado permite filtrar por usuario, empresa, rol.
- El administrador puede crear una asignación: seleccionar usuario, empresa, rol.
- La combinación (IDRol, IDEmpresa, IDUsuario) debe ser única.
- El administrador puede editar un permiso (cambiar el rol).
- El administrador puede eliminar un permiso (quitar acceso del usuario a esa empresa).
- Un usuario sin permisos en ninguna empresa no puede acceder al sistema tras el login.
- Se validan que usuario, empresa y rol existan antes de crear.

## Tabla involucrada

- `Pq_Permiso`: id, IDRol, IDEmpresa, IDUsuario (PK compuesta: IDRol, IDEmpresa, IDUsuario)

## Reglas de negocio

- Solo administradores pueden gestionar permisos.
- Un usuario puede tener múltiples permisos (una por cada empresa en la que opera).

## Dependencias

- HU-001 (Login)
- HU-010 (Usuarios), HU-011 (Empresas), HU-012 (Roles)
- Tablas `users`, `PQ_Empresa`, `Pq_Rol`, `Pq_Permiso`