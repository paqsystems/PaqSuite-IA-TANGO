# HU-003 – Apertura de opción de menú en misma o nueva pestaña

## Épica
000 – Generalidades

## Clasificación
SHOULD-HAVE

## Rol
Usuario del sistema (frontend web)

## Narrativa

Como usuario que trabajo en el frontend web quiero elegir si las opciones del menú se abren en la misma pestaña del navegador o en una nueva para adaptar mi flujo de trabajo (ej. tener varios procesos abiertos en paralelo).

## Criterios de aceptación

- El usuario puede configurar una preferencia: "Abrir en misma pestaña" (por defecto) o "Abrir en nueva pestaña".
- La preferencia se aplica al hacer clic en cualquier opción de menú que navega a un proceso.
- Si la preferencia es "misma pestaña": la navegación ocurre en la pestaña actual (comportamiento estándar de SPA).
- Si la preferencia es "nueva pestaña": el proceso se abre en una nueva pestaña del navegador (`target="_blank"` o equivalente).
- La preferencia se persiste en el campo `users.menu_abrir_nueva_pestana` (Dictionary DB) para que se mantenga ante nuevo acceso, en el mismo navegador, otro navegador u otro PC.
- La opción de configuración está disponible en el menú de usuario (dropdown, configuración o perfil).
- **Esta funcionalidad aplica solo al frontend web.** En aplicaciones móviles (app nativa o PWA en modo mobile) no se ofrece ni se aplica.

## Alcance técnico

- Frontend web (React/SPA): sí.
- Mobile (app nativa, PWA mobile): no aplica; el menú siempre navega en el mismo contexto.

## Reglas de negocio

- Por defecto: "misma pestaña" (comportamiento actual de la SPA).
- La preferencia es por usuario, no por empresa.
- La preferencia debe mantenerse ante nuevo acceso del usuario, en el mismo navegador, otro navegador u otro PC (persistencia server-side).
- Al abrir en nueva pestaña, la nueva pestaña debe mantener el contexto de sesión (token, empresa activa) para que el usuario no tenga que autenticarse de nuevo.

## Dependencias

- Layout principal con menú de navegación implementado.
- Menú de usuario o área de configuración donde exponer la opción.
- HU-001 (Login) de épica 001 – sesión activa.
- Campo `users.menu_abrir_nueva_pestana` en Dictionary DB; API para leer/actualizar (incluido en payload de login o endpoint de preferencias).

## Tabla involucrada

- `users`: campo `menu_abrir_nueva_pestana` (bit, 0 = misma pestaña, 1 = nueva pestaña)

## Referencias

- `docs/modelo-datos/md-diccionario/md-diccionario.md` – Esquema users
- `specs/ui/screen-specifications.md` – Opción "Abrir en otra solapa" en menú usuario
- `docs/01-arquitectura/ui/01_MainLayout_PostLogin_Specification.md` – TabPanel y modo solapas (desktop/tablet)
