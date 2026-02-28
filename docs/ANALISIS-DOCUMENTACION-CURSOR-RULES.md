# Análisis: Documentación docs/ vs .cursor/rules

**Fecha:** 2025-02-23  
**Objetivo:** Revisar coherencia entre `docs/` y `.cursor/rules/`, aplicar correcciones y mejoras.

---

## Resumen de cambios realizados

### 1. `.cursor/rules/21-Iniciar-tunel-SSH-para-MySql.md`
- **Problema:** Ruta del proyecto incorrecta (`Lidr-AI4Devs2025-ProyectoFinal`).
- **Corrección:** Actualizada a `C:\Programacion\PaqSuite-IA-ERP`.

### 2. `.cursor/rules/01-project-context.md`
- **Problema:** Decía "No es un ERP", contradiciendo `docs/00-contexto/`.
- **Corrección:** Actualizado para reflejar plataforma ERP en evolución, modelo multiempresa, módulo Partes, referencias a `docs/00-contexto/` y `docs/01-arquitectura/`.

### 3. `.cursor/rules/08-security-sessions-tokens.md`
- **Problema:** No mencionaba X-Company-Id, tenant, Dictionary DB vs Company DB.
- **Corrección:** Añadida sección "0) Modelo multiempresa y tenant" con header `X-Company-Id`, validación obligatoria y referencias a docs de arquitectura.

### 4. `.cursor/rules/05-backend-policy.md`
- **Problema:** Restricción obsoleta "NO programar" (fase de documentación).
- **Corrección:** Eliminada la restricción; añadida sección "Referencias" con enlaces a docs de arquitectura.

### 5. `docs/README.md`
- **Problema:** Estructura desactualizada (producto.md, historias-y-tickets.md en raíz; hu-historias/ en raíz).
- **Corrección:** Reescrito para reflejar estructura real: `00-contexto/`, `01-arquitectura/`, `_projects/SistemaPartes/`, tabla de reglas .cursor, orden de lectura.

### 6. `AGENTS.md`
- **Problema:** Referencias a `docs/producto.md`, `docs/historias-y-tickets.md` inexistentes.
- **Corrección:** Actualizadas referencias a `docs/00-contexto/`, `docs/01-arquitectura/`, `docs/_projects/SistemaPartes/`.

### 7. `docs/00-contexto/03-checklist-proyecto-erp.md`
- **Problema:** Referencia a `contexto-global-erp.md` (archivo renombrado a `00-contexto-global-erp.md`).
- **Corrección:** Actualizada la ruta.

### 8. `docs/00-contexto/02-guia-onboarding-30-minutos.md`
- **Problema:** Referencias a archivos con nombres antiguos.
- **Corrección:** Actualizadas a `00-contexto-global-erp.md`, `01-guia-estructura-documental-corporativa.md`, `01-arquitectura-proyecto.md`.

---

## Observaciones (sin cambios)

### Estructura documental planificada vs real
- **docs/01-guia-estructura-documental-corporativa.md** describe carpetas `02-producto/`, `03-hu-historias/`, `04-tareas/` que no existen aún.
- **Realidad:** Las HU y TR están en `docs/_projects/SistemaPartes/hu-historias/` y `hu-tareas/`.
- **Recomendación:** Decidir si migrar a la estructura corporativa o actualizar la guía para reflejar la estructura actual.

### 03-general-quality.md
- Menciona "Integración con al menos un MCP server" y "Docker Compose funcionando" como entregables.
- **docs/deploy-ci-cd.md** indica que Docker está desactivado.
- **Recomendación:** Revisar si estos entregables aplican al estado actual del proyecto.

### 07-frontend-norms.md
- Menciona "Tailwind CSS + shadcn/ui" como UI Library.
- El proyecto usa DevExtreme (según package.json) aunque con uso limitado.
- **Recomendación:** Verificar stack real del frontend y actualizar si corresponde.

### 09-data-access-orm-sql.md
- Referencia `docs/domain/DATA_MODEL.md` — **existe** y está correcta.

---

## Referencias cruzadas añadidas

- **05-backend-policy.md** → docs/01-arquitectura, backend, 06-api-contract, 08-security
- **08-security-sessions-tokens.md** → docs/01-arquitectura (seguridad, tenancy)
- **01-project-context.md** → docs/00-contexto, docs/01-arquitectura, docs/_projects/SistemaPartes
- **docs/README.md** → Tabla de reglas .cursor/rules con propósito de cada una

---

## Conclusión

Se han aplicado correcciones para alinear `.cursor/rules/` con la documentación en `docs/`, especialmente:

1. Coherencia entre visión ERP (docs) y contexto del proyecto (rules).
2. Rutas y referencias actualizadas.
3. Modelo multiempresa y tenant documentado en seguridad.
4. Eliminación de restricciones obsoletas.
5. Referencias cruzadas entre reglas y documentación.
