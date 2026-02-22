# Instalación y Configuración de Playwright

**Versión instalada:** 1.57.0  
**Fecha de instalación:** 2025-01-20

---

## Estado de la Instalación

✅ **Playwright está instalado y configurado** en `frontend/`

### Archivos de Configuración Creados

- ✅ `package.json` - Dependencias y scripts de Playwright
- ✅ `playwright.config.ts` - Configuración completa de Playwright
- ✅ `tsconfig.json` - Configuración TypeScript
- ✅ `tsconfig.node.json` - Configuración TypeScript para Node
- ✅ `vite.config.ts` - Configuración de Vite
- ✅ `.gitignore` - Ignora archivos generados por Playwright

### Tests

- ✅ `tests/e2e/example.spec.ts` - Test de ejemplo
- ✅ `tests/e2e/README.md` - Documentación de tests E2E

### Documentación

- ✅ `README.md` - Guía del frontend (incluye información de Playwright)
- ✅ `tests/e2e/README.md` - Convenciones de tests E2E

---

## Verificación de Instalación

Para verificar que Playwright está correctamente instalado:

```bash
# Verificar versión
npx playwright --version

# Listar tests disponibles
npx playwright test --list
```

---

## Instalación de Navegadores

Si los navegadores no están instalados, ejecutar:

```bash
# Instalar todos los navegadores (Chromium, Firefox, WebKit)
npx playwright install

# O instalar solo Chromium (más rápido)
npx playwright install chromium
```

---

## Scripts Disponibles

```bash
npm run test:e2e          # Ejecutar todos los tests E2E
npm run test:e2e:ui      # Ejecutar con UI interactiva
npm run test:e2e:headed # Ejecutar viendo el navegador
npm run test:e2e:debug   # Ejecutar en modo debug
```

---

## Verificar que Todo Funciona

Ejecutar el test de ejemplo:

```bash
npm run test:e2e
```

Si todo está correcto, deberías ver:
- ✅ El test se ejecuta
- ✅ Se abre el navegador (si usas `--headed`)
- ✅ El test pasa o falla según corresponda

---

## Solución de Problemas

### Error: "Cannot find module '@playwright/test'"

```bash
cd frontend
npm install
```

### Error: "Executable doesn't exist"

```bash
npx playwright install
```

### Los tests no encuentran la aplicación

Verificar que:
1. El servidor de desarrollo está corriendo en `http://localhost:3000`
2. O configurar `PLAYWRIGHT_BASE_URL` en variables de entorno

```bash
# Windows PowerShell
$env:PLAYWRIGHT_BASE_URL="http://localhost:3000"
npm run test:e2e

# Linux/Mac
PLAYWRIGHT_BASE_URL=http://localhost:3000 npm run test:e2e
```

---

## Próximos Pasos

Una vez que Playwright esté instalado:

1. **Instalar navegadores** (si aún no están instalados):
   ```bash
   cd frontend
   npx playwright install
   ```

2. **Revisar el flujo E2E:**
   - `specs/flows/e2e-core-flow.md` - Flujo E2E documentado paso a paso

3. **Revisar especificaciones de tests:**
   - `specs/tests/e2e/` - Especificaciones de tests E2E
   - `frontend/tests/e2e/README.md` - Convenciones de tests

4. **Implementar tests reales:**
   - Seguir las especificaciones en `specs/tests/e2e/`
   - Usar `data-testid` en todos los componentes
   - Seguir las reglas en `.cursor/rules/11-playwright-testing-rules.md`

---

## Referencias

- [Documentación oficial de Playwright](https://playwright.dev)
- `docs/frontend/testing.md` - Estrategia de testing del proyecto
- `frontend/tests/e2e/README.md` - Convenciones de tests E2E
- `.cursor/rules/11-playwright-testing-rules.md` - Reglas obligatorias de testing
- `specs/flows/e2e-core-flow.md` - Flujo E2E a testear
