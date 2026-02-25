# Appium – E2E Testing para Apps Mobile (Capacitor)

> **Estado:** No instalado. Este documento define la estrategia y normativas para cuando se decida implementar testing E2E nativo en apps Capacitor.

---

## 1) Alcance del Documento

Este documento recopila:

- Criterios de cuándo instalar Appium
- Comparación Appium vs Detox
- Configuración MCP para integración con Cursor
- Requisitos previos y flujo de setup
- Relación con Playwright (web vs native)

---

## 2) Playwright vs Appium – Qué Cubre Cada Uno

| Herramienta | Alcance | Estado actual |
|-------------|---------|---------------|
| **Playwright** | Web (incl. viewport mobile) | ✅ Instalado y configurado |
| **Appium** | App nativa empaquetada (APK/IPA) en emulador/dispositivo | ❌ No instalado |

**Playwright** prueba la app web en navegador, incluyendo emulación de viewport móvil. Cubre la lógica y el layout responsive.

**Appium** prueba la app Capacitor empaquetada (WebView dentro de contenedor nativo) en emulador o dispositivo real. Cubre gestos nativos, notificaciones, integración con el sistema, etc.

---

## 3) Appium vs Detox – Por Qué Appium

| Criterio | Appium | Detox |
|----------|--------|-------|
| **Target principal** | Apps nativas e híbridas (WebView) | React Native |
| **Capacitor** | Soporte directo (WebView en Android/iOS) | Soporta WebViews, pero está pensado para RN |
| **Stack del proyecto** | React + Capacitor = WebView | No es React Native |
| **Madurez en Capacitor** | Estándar de facto para apps híbridas | Menos documentado para Capacitor |
| **MCP disponible** | Sí: `@gavrix/appium-mcp` | No hay MCP oficial conocido |

**Decisión:** Para apps Capacitor (React en WebView), **Appium** es la opción recomendada. Detox está optimizado para React Native; este proyecto usa React web empaquetado con Capacitor.

---

## 4) Cuándo Instalar Appium

**Recomendación:** Diferir la instalación hasta que mobile sea prioridad real.

### Motivos para diferir

1. **Un solo código base:** Playwright con viewport mobile ya cubre la lógica y el layout mobile en web.
2. **Coste de setup:** Appium requiere emuladores, drivers, configuración de CI, etc.
3. **MVP:** Si el foco es web o el uso mobile es bajo, el retorno puede ser pequeño.
4. **Mantenimiento:** Más herramientas implican más mantenimiento.

### Cuándo sí instalarlo

- Cuando mobile sea canal principal o crítico para el producto.
- Cuando se necesiten validar gestos nativos, notificaciones push, integración con el sistema.
- Cuando el CI deba ejecutar tests en emuladores/dispositivos reales.
- Cuando Playwright con viewport mobile no alcance para validar el comportamiento en la app empaquetada.

---

## 5) Configuración MCP para Appium

Cuando se instale Appium, configurar el MCP para integración con Cursor.

### Servidor MCP

- **Paquete:** `@gavrix/appium-mcp`
- **Repositorio:** [gavrix/appium-mcp](https://github.com/gavrix/appium-mcp)

### Herramientas que expone

- **Session Management:** Iniciar/finalizar sesiones con detección automática de dispositivo
- **App Control:** Lanzar la app por bundle ID (iOS) o package name (Android)
- **Element Interaction:** Buscar elementos, tap, ingresar texto, obtener contenido
- **Screen Capture:** Screenshots y page source XML
- **Device Interaction:** Gestos, botón home, logs del dispositivo
- **Plataformas:** iOS simulator y Android emulator/device

### Configuración en Cursor

En **Cursor Settings** > **Features** > **MCP**, agregar:

```json
{
  "appium": {
    "command": "npx",
    "args": ["-y", "@gavrix/appium-mcp"]
  }
}
```

O en el archivo de configuración MCP del proyecto (según estructura local).

### Requisitos previos para MCP

- Appium instalado y configurado
- Emulador o dispositivo conectado
- App compilada (APK para Android, IPA/simulator para iOS)

---

## 6) Requisitos de Setup (Cuando se Instale)

### Dependencias

- **Node.js:** 20+
- **Appium:** `npm install -g appium` o `npx appium`
- **Drivers:** Appium UIAutomator2 (Android), XCUITest (iOS)
- **Android:** Android Studio, emulador AVD o dispositivo físico, `adb`
- **iOS:** Xcode (solo macOS), simulador o dispositivo

### Flujo de ejecución

1. Compilar la app: `npm run build` en `frontend/`
2. Sincronizar con Capacitor: `npm run cap:sync`
3. Generar APK/IPA o abrir en Android Studio/Xcode
4. Iniciar emulador o conectar dispositivo
5. Ejecutar tests Appium contra la app instalada

### Integración con data-testid

Los tests Appium pueden reutilizar los `data-testid` definidos para Playwright, ya que la app Capacitor renderiza el mismo HTML/React. Los selectores por `data-testid` funcionan dentro del WebView.

---

## 7) Normativas para Tests Appium (Futuro)

Cuando se implemente, aplicar las mismas convenciones que Playwright:

- **Selectores:** Preferir `data-testid` sobre CSS/XPath/texto
- **Idempotencia:** Tests independientes, sin dependencias entre sí
- **Esperas:** Evitar esperas ciegas; esperar estados explícitos
- **Nomenclatura:** `debe [acción] [condición/resultado]`
- **Organización:** Agrupar por feature con `describe`

Ver `.cursor/rules/11-playwright-testing-rules.md` como referencia; adaptar para Appium cuando corresponda.

---

## 8) Referencias

- **Playwright (actual):** `.cursor/rules/11-playwright-testing-rules.md`, `frontend/tests/e2e/`
- **Mobile:** `docs/mobile/README.md`, `.cursor/rules/07b-frontend-mobile-norms.md`
- **Appium:** [appium.io](https://appium.io/)
- **Appium MCP:** [gavrix/appium-mcp](https://github.com/gavrix/appium-mcp)
- **Capacitor:** [capacitorjs.com](https://capacitorjs.com/)

---

**Última actualización:** 2025-02-23
