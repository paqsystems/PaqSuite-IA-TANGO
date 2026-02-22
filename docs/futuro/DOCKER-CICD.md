# Docker y CI/CD – Documentación para acción futura

## Estado actual

Los workflows de **CI** y **CD** están **desactivados**. Los archivos se encuentran en `docs/futuro/workflows/` para que no se ejecuten en push o pull request.

Docker y los Dockerfiles (`backend/Dockerfile`, `frontend/Dockerfile`, `docker/docker-compose.yml`) permanecen en su ubicación actual y están listos para usarse cuando se decida habilitarlos.

---

## Pasos para habilitar CI/CD

### 1. Restaurar workflows en GitHub Actions

Copiar los archivos de workflows a la carpeta activa:

```bash
cp docs/futuro/workflows/ci.yml .github/workflows/ci.yml
cp docs/futuro/workflows/cd.yml .github/workflows/cd.yml
```

Los workflows en `docs/futuro/workflows/` ya tienen las referencias actualizadas:
- Base de datos de tests: `paqsuite_ia_erp_test` (en lugar de `lidr_test`)

### 2. Verificar configuración

- **CI** (`ci.yml`): Se ejecuta en `push` y `pull_request` a `main`, y manualmente con `workflow_dispatch`.
- **CD** (`cd.yml`): Se ejecuta en `push` a `main` y publica imágenes en `ghcr.io/<owner>/paqsuite-backend` y `paqsuite-frontend`.

### 3. Requisitos previos

- Repositorio en GitHub con permisos para GitHub Actions.
- Para CD: permisos de escritura en GitHub Container Registry (por defecto con `GITHUB_TOKEN`).

---

## Pasos para habilitar Docker

Los Dockerfiles y `docker-compose.yml` ya están configurados. Para usarlos:

```bash
cd docker
cp .env.example .env
# Editar .env con credenciales de BD externa (DB_HOST, DB_DATABASE, etc.)
docker compose up -d
```

- Backend: `http://localhost:8000`
- Frontend: `http://localhost:80`

Ver `docs/deploy-ci-cd.md` para más detalles.
