# HU-002 – Cambio de empresa activa

## Épica
000 – Generalidades

## Clasificación
MUST-HAVE

## Rol
Usuario del sistema con acceso a más de una empresa

## Narrativa

Como usuario que tengo acceso a varias empresas quiero cambiar la empresa activa sin cerrar sesión para operar en otra empresa sin tener que volver a autenticarme.

## Criterios de aceptación

- El usuario puede cambiar la empresa activa desde la barra superior o menú principal (selector de empresa).
- Solo se muestran las empresas a las que el usuario tiene permiso (`Pq_Permiso` con IDUsuario e IDEmpresa).
- Al seleccionar otra empresa, el sistema actualiza el contexto (empresa activa) y recarga o ajusta la vista actual.
- El cambio se refleja en el header/barra (nombre de empresa visible).
- Las peticiones subsiguientes a la API incluyen la nueva empresa activa (ej. header `X-Company-Id`).
- Si el usuario tiene una sola empresa, el selector puede ocultarse o mostrarse deshabilitado (solo lectura).
- El cambio de empresa no invalida el token de sesión; el usuario permanece autenticado.

## Tablas involucradas

- `Pq_Permiso`: id, IDEmpresa, IDUsuario, IDRol – para listar empresas permitidas
- `PQ_Empresa`: IDEmpresa, NombreEmpresa – para mostrar el nombre de cada empresa

## Reglas de negocio

- Solo se pueden seleccionar empresas en las que el usuario tiene al menos un permiso.
- La empresa activa se mantiene en el frontend (estado global, ej. store/context) y se envía en cada request.
- El backend valida que el usuario tenga permiso para la empresa indicada en `X-Company-Id` antes de ejecutar operaciones.

## Dependencias

- HU-001 (Login) de épica 001 – flujo de autenticación y permisos
- Layout principal implementado con barra superior o área de cabecera
- API que acepte y valide `X-Company-Id`

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema Pq_Permiso, PQ_Empresa
- `docs/01-arquitectura/06-mapa-visual-seguridad-roles-permisos-menu.md` – Flujo de autorización
