# HU-002 – Selección de empresa activa

## Épica
001 – Seguridad y Acceso

## Clasificación
MUST-HAVE

## Rol
Usuario con acceso a múltiples empresas

## Narrativa

Como usuario asignado a varias empresas quiero seleccionar la empresa activa para operar en su contexto y que el sistema cargue los permisos y el menú correspondientes.

## Criterios de aceptación

- El usuario autenticado puede ver la lista de empresas a las que tiene acceso (según `Pq_Permiso`).
- La lista muestra nombre de empresa y, opcionalmente, imagen/theme.
- El usuario puede seleccionar una empresa de la lista.
- Al seleccionar, el sistema establece el contexto de empresa activa (tenant).
- El frontend envía el header `X-Company-Id` en todas las peticiones subsiguientes.
- El sistema recarga el menú según los permisos del usuario en esa empresa.
- El sistema aplica el theme de la empresa seleccionada (si está configurado).
- La empresa activa se muestra en el TopBar (texto truncable si es largo).
- El usuario puede cambiar de empresa en cualquier momento desde el menú de usuario (Cambiar Empresa).
- Si el usuario intenta acceder con un `X-Company-Id` no autorizado, el sistema responde 403.
- La selección de empresa se persiste en sesión/localStorage hasta logout o cambio explícito.

## Reglas de negocio

- Solo se muestran empresas donde el usuario tiene al menos un permiso en `Pq_Permiso`.
- La validación de `X-Company-Id` es obligatoria en cada request de gestión.
- No se confía en el header sin validar contra las asignaciones del usuario.

## Dependencias

- HU-001 (Login)
- Tablas `PQ_Empresa`, `Pq_Permiso`
- Layout principal con TopBar y menú de usuario

## Referencias

- `docs/ui/mockups/mockup-spec-mainlayout.md` – M03_CompanySwitcher_Popup, TopBar Empresa Activa
- `docs/01-arquitectura/07-mapa-visual-tenancy-resolucion-db.md` – Resolución de tenant
- `.cursor/rules/08-security-sessions-tokens.md` – X-Company-Id
