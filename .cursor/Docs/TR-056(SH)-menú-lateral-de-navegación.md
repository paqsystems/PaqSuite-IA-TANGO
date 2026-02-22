# Documentación: TR-056(SH) – Menú lateral de navegación

## Ubicación
Derivado de `docs/hu-historias/HU-056(SH)-menú-lateral-de-navegación.md`.

## Propósito
Implementar el menú lateral izquierdo en todas las pantallas autenticadas: ítems por rol, orden (Inicio, Archivos, Partes, Informes), colapsable en móvil, estado activo y sin bloque de botones de acceso rápido en el dashboard.

## Implementación
- **Sidebar.tsx / Sidebar.css:** menú con NavLink, agrupaciones y separadores; visibilidad por rol (getUserData).
- **AppLayout.tsx / AppLayout.css:** integración del Sidebar, botón hamburguesa (app.sidebarToggle) en móvil, layout flex (header + body con sidebar + main).
- **Dashboard.tsx:** eliminado el bloque `welcome-card-actions` (botones de acceso rápido); se mantiene la tarjeta de bienvenida y el resumen ejecutivo.
- **E2E:** test en `dashboard.spec.ts`: "TR-056: menú lateral visible; enlaces Inicio y Perfil presentes" (viewport 1024x768).

## Criterios de aceptación cubiertos
- Menú lateral fijo a la izquierda (desktop) o overlay colapsable (móvil).
- Opciones del dashboard reubicadas como ítems del menú.
- Visibilidad por rol (empleado, supervisor, cliente).
- Colapsable en pantallas pequeñas (botón menú).
- Estado activo (sidebar-link-active) según ruta.
- Dashboard sin bloque de botones de acceso rápido.
- data-testid en enlaces del menú.
