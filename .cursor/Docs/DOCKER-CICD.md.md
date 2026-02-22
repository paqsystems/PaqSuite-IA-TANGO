# Documentación: docs/futuro/DOCKER-CICD.md

## Propósito

Archivo de documentación para acción futura que explica cómo habilitar Docker y CI/CD cuando se requiera. Los workflows de GitHub Actions están desactivados (ubicados en `docs/futuro/workflows/`) y este documento describe los pasos para restaurarlos.

## Contenido principal

- **Estado actual:** CI/CD desactivados; workflows en `docs/futuro/workflows/`
- **Habilitar CI/CD:** Copiar `ci.yml` y `cd.yml` a `.github/workflows/`
- **Referencias actualizadas:** Base de datos de tests `paqsuite_ia_erp_test`
- **Habilitar Docker:** Uso de `docker/docker-compose.yml` con variables de entorno

## Relación con el plan de migración

Creado como parte de la migración a PaqSuite-IA-ERP, donde se decidió no implementar Docker ni CI/CD de inmediato, dejando todo documentado para habilitar en el futuro.
