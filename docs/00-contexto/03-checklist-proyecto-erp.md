# Checklist Corporativo – Inicio y Desarrollo de Proyecto ERP

**Nivel : Operativo (Uso General)**

## Objetivo

Este checklist define el proceso estándar para iniciar y desarrollar módulos dentro del ERP corporativo.

Debe utilizarse como guía obligatoria antes, durante y después del desarrollo.

---

# FASE 1 – Contexto y Alcance

Antes de escribir código:

- [ ] Leer `docs/00-contexto/00-contexto-global-erp.md`
- [ ] Confirmar comprensión del modelo multiusuario, multiempresa y multirroles
- [ ] Definir objetivo conceptual del módulo
- [ ] Definir alcance (qué incluye y qué no incluye)
- [ ] Identificar roles impactados
- [ ] Documentar alcance en `docs/02-producto/`

No avanzar sin alcance definido.

---

# FASE 2 – Flujo E2E Prioritario

- [ ] Definir flujo completo con inicio y fin claros
- [ ] Validar que el flujo genera valor funcional completo
- [ ] Confirmar que el flujo respeta el modelo de seguridad y tenant
- [ ] Documentar en `docs/02-producto/flujos-e2e.md`

El proyecto siempre debe comenzar por un flujo E2E operativo.

---

# FASE 3 – Arquitectura

Antes de implementar:

- [ ] Evaluar impacto arquitectónico
- [ ] Verificar si afecta:
  - Seguridad
  - Tenancy
  - Modelo de datos
  - Separación diccionario / empresa
- [ ] Documentar en `docs/01-arquitectura/`
- [ ] Crear ADR si la decisión es estructural

Nunca tomar decisiones estructurales sin documentarlas.

---

# FASE 4 – Historias de Usuario (HU)

- [ ] Crear HU en `docs/03-historias-usuario/`
- [ ] Redactar con formato:
  - Como [rol]
  - Quiero [acción]
  - Para [beneficio]
- [ ] Definir criterios de aceptación claros
- [ ] Clasificar MUST / SHOULD
- [ ] Validar que las HU cubren el flujo E2E

No programar sin HU aprobada.

---

# FASE 5 – Tareas Técnicas (TR)

- [ ] Derivar TR desde HU
- [ ] Clasificar por tipo:
  - Backend
  - Frontend
  - DB
  - Tests
  - Docs
- [ ] Documentar en `docs/04-tareas/`
- [ ] Verificar trazabilidad HU → TR

No crear tareas sueltas sin HU asociada.

---

# FASE 6 – Implementación

- [ ] Crear rama con referencia a HU
- [ ] Implementar respetando separación de capas
- [ ] No mezclar lógica de negocio en controllers
- [ ] Documentar clases y métodos
- [ ] Mantener coherencia con arquitectura

El código debe reflejar la documentación.

---

# FASE 7 – Testing

Obligatorio por cada flujo crítico:

- [ ] Unit tests (Services / Domain)
- [ ] Integration tests (API + DB)
- [ ] Al menos 1 Test E2E (happy path)
- [ ] Actualizar matriz de trazabilidad

Sin tests mínimos, la tarea no está completa.

---

# FASE 8 – Validación Final

Antes de cerrar:

- [ ] Flujo E2E operativo
- [ ] Tests en verde
- [ ] Documentación actualizada
- [ ] No hay decisiones técnicas sin registrar
- [ ] No hay código sin HU asociada

---

# Reglas de Oro

1. No programar sin HU.
2. No crear tareas sin HU.
3. No modificar arquitectura sin documentarlo.
4. No cerrar tareas sin tests.
5. La documentación es parte del entregable.

---

# Orden de Prioridad

1. Contexto y alcance
2. E2E
3. HU
4. TR
5. Código
6. Tests
7. Validación final

El orden es parte de la disciplina profesional del proyecto.
