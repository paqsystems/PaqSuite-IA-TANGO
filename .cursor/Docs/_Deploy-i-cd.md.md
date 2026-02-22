# Documentación: _Deploy-i-cd.md

## Propósito
Archivo resumen de la implementación del pipeline CI/CD con GitHub Actions. Recopila lo configurado en el chat de configuración, sin incluir deploy automático a producción.

## Ubicación
`_Deploy-i-cd.md` (raíz del proyecto)

## Contenido principal
- Lista de archivos creados o modificados para el pipeline
- Descripción de los 4 jobs (backend, frontend, swagger, e2e)
- Rama principal: `main`
- Herramientas usadas en el runner de GitHub
- Lo que **no** se implementó (deploy automático)
- Comandos para validar localmente
- Referencias a docs relacionadas

## Relación con otros documentos
- `docs/deploy-ci-cd.md` – Detalle técnico de infra y CI/CD
- `.github/workflows/ci.yml` – Definición del workflow
- `_Proyecto-final-cumplimiento.md` – Actualizado para reflejar que el pipeline está programado excepto el deploy automático
