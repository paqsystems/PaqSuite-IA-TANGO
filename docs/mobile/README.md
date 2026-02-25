# Frontend Mobile (Capacitor)

El frontend React se empaqueta como app nativa iOS/Android mediante Capacitor. Un solo código base para web y mobile.

## Requisitos

- Node.js 20+
- Android Studio (para Android)
- Xcode (para iOS, solo macOS)
- Java JDK 17 (para Android)

## Comandos

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

## Configuración API

En modo app, la URL base de la API debe apuntar al servidor (no localhost). Configurar en variables de entorno:

- `VITE_API_URL` para build de producción
- En desarrollo con livereload: `CAPACITOR_DEV_SERVER=http://<tu-ip>:3000`

## Flujo de build

1. `npm run build` - Vite genera `dist/`
2. `cap sync` - Copia `dist/` a proyectos Android/iOS
3. Abrir en Android Studio / Xcode y ejecutar

## Testing E2E mobile (Appium)

Para testing E2E en la app empaquetada (emulador/dispositivo), ver `docs/mobile/APPIUM-E2E-TESTING.md`. Appium no está instalado por defecto; el documento define cuándo y cómo implementarlo.
