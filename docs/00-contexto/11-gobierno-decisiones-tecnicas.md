# Gobierno de Decisiones Técnicas

**Nivel : Estratégico (gobierno y arquitectura)**

## 1. Objetivo

Establecer cómo se toman decisiones técnicas dentro del ERP.

Evitar:

- Decisiones improvisadas
- Cambios emocionales
- Modificaciones por presión comercial
- Inconsistencias estructurales

---

## 2. Clasificación de Decisiones

### Nivel 1 – Operativas
Cambios menores que no afectan arquitectura.
Ej: agregar campo no crítico.

No requieren registro especial.

---

### Nivel 2 – Arquitectónicas
Cambios que afectan:

- Modelo de datos
- Seguridad
- Tenancy
- Estructura de módulos
- Integraciones externas

Requieren documentación formal y aprobación del Arquitecto.

---

### Nivel 3 – Estratégicas
Cambios que afectan:

- Modelo multiempresa
- Modelo de autenticación
- Separación de bases
- Arquitectura base
- Estructura corporativa

Requieren:

- Evaluación profunda
- Documentación
- Plan de migración

---

## 3. Proceso de Decisión

1. Identificar impacto.
2. Evaluar riesgos.
3. Documentar justificación.
4. Definir consecuencias.
5. Aprobar o rechazar.

Nunca decidir directamente en código.

---

## 4. Criterios de Evaluación

Toda decisión debe evaluarse bajo:

- Seguridad
- Escalabilidad
- Mantenibilidad
- Performance
- Simplicidad
- Coherencia con modelo conceptual

---

## 5. Regla de Oro

La urgencia comercial no justifica romper arquitectura.

Si una decisión rompe principios estructurales,
se busca alternativa.
