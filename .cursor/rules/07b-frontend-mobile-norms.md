---
alwaysApply: true
---
# description: Normas y Convenciones de Frontend Mobile (Capacitor)

## Alcance

Este documento complementa `.cursor/rules/07-frontend-norms.md` con reglas específicas para el frontend mobile empaquetado con Capacitor.

**Modelo:** Un solo código base (`frontend/`) sirve web y mobile. Capacitor empaqueta el build (`dist/`) como app nativa iOS/Android.

---

## Stack Mobile

- **Capacitor:** @capacitor/core, @capacitor/cli, @capacitor/android, @capacitor/ios
- **Mismo código:** React + Vite + DevExtreme (responsivo)
- **Breakpoints:** Mobile < 768px, Tablet 768–1024px, Desktop > 1024px

---

## Reglas de Desarrollo Mobile

### 1) Detección de entorno

Usar `Capacitor.isNativePlatform()` o similar para detectar si la app corre en nativo:

```typescript
import { Capacitor } from '@capacitor/core';

const isNative = Capacitor.isNativePlatform();
```

- En web: `isNative === false`
- En app iOS/Android: `isNative === true`

### 2) URL base de la API

- **Web:** Puede usar rutas relativas o `VITE_API_URL` (ej: `http://localhost:8000/api`).
- **Mobile (app):** Debe apuntar al servidor real (no `localhost`). Configurar en `.env`:
  - `VITE_API_URL` para build de producción (ej: `https://api.tudominio.com/api`).
  - En desarrollo con livereload: `CAPACITOR_DEV_SERVER=http://<tu-ip>:3000` para que la app cargue desde el dev server.

**Regla:** El cliente HTTP debe leer `import.meta.env.VITE_API_URL` y usarlo como base. En mobile, nunca asumir `localhost`.

### 3) Layout y pantallas mobile

- **Mobile:** Sin dashboard, sin solapas (TabPanel). Priorizar "ejecución de procesos".
- **MobileHome:** Pantalla de quick access (acceso rápido a procesos), no versión simplificada del dashboard.
- **Drawer:** En mobile, el menú lateral es Drawer (fullscreen modal, swipe/backdrop para cerrar).
- Ver `docs/01-arquitectura/ui/01_MainLayout_PostLogin_Specification.md` para reglas detalladas.

### 4) Almacenamiento

- **Token/Sesión:** `localStorage` o `sessionStorage` (funciona en Capacitor).
- **Preferencias:** Si se requieren APIs nativas (ej: preferencias del sistema), usar plugins de Capacitor (`@capacitor/preferences`).

### 5) Componentes DevExtreme en mobile

- DevExtreme es responsivo por defecto.
- Usar componentes adaptativos: `Drawer`, `Popup`, `ActionSheet` para sensación nativa en mobile.
- Evitar componentes pesados (grillas con miles de filas) sin virtualización.

### 6) TestId y accesibilidad

- Las mismas reglas que web: `data-testid` obligatorio, ARIA cuando aplique.
- En mobile, considerar gestos (swipe) además de navegación por teclado.

---

## Comandos Capacitor

Ejecutar desde `frontend/`:

```bash
# Añadir plataformas (solo primera vez)
npm run cap:init

# Build + sincronizar con proyectos nativos
npm run cap:sync

# Ejecutar en Android
npm run cap:android

# Ejecutar en iOS (macOS)
npm run cap:ios
```

**Flujo de build mobile:**
1. `npm run build` – Vite genera `dist/`
2. `npm run cap:sync` – Copia `dist/` a proyectos Android/iOS
3. Abrir en Android Studio / Xcode y ejecutar

---

## Requisitos de entorno

- Node.js 20+
- Android Studio (para Android)
- Xcode (para iOS, solo macOS)
- Java JDK 17 (para Android)

---

## Referencias

- `docs/mobile/README.md` – Build, comandos y configuración
- `docs/01-arquitectura/ui/01_MainLayout_PostLogin_Specification.md` – Reglas mobile (sin dashboard, sin solapas)
- `docs/arquitectura.md` – Visión general web + mobile
