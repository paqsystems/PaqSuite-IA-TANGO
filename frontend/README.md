# Frontend - Sistema de Registro de Tareas

## Instalación

```bash
npm install
```

## Desarrollo

```bash
npm run dev
```

La aplicación estará disponible en `http://localhost:3000`

## Testing

### Tests Unitarios

```bash
npm run test
npm run test:watch
npm run test:coverage
```

### Tests E2E con Playwright

```bash
# Ejecutar todos los tests E2E
npm run test:e2e

# Ejecutar con UI interactiva
npm run test:e2e:ui

# Ejecutar en modo headed (ver el navegador)
npm run test:e2e:headed

# Ejecutar en modo debug
npm run test:e2e:debug
```

## Estructura

```
frontend/
├── src/              # Código fuente
├── tests/            # Tests
│   └── e2e/         # Tests E2E con Playwright
├── playwright.config.ts  # Configuración de Playwright
└── package.json
```

## Configuración de Playwright

La configuración de Playwright está en `playwright.config.ts`. 

**Variables de entorno:**
- `PLAYWRIGHT_BASE_URL`: URL base de la aplicación (default: http://localhost:3000)
- `CI`: Si está definido, ejecuta en modo CI (más estricto)

## Convenciones de Testing E2E

- Todos los controles interactivos deben tener `data-testid`
- Usar selectores por `data-testid` en lugar de CSS/XPath
- Seguir el patrón Arrange-Act-Assert
- Ver `docs/frontend/testing.md` para más detalles

