# Regla: Orquestador HU → TR (BORRADOR – NO ACTIVA)

> ⚠️ **ESTADO: DOCUMENTACIÓN / COMENTARIO**
>
> Esta regla **NO está en práctica**. Es un borrador para planificación futura.
> El asistente NO debe ejecutar este flujo hasta que se indique explícitamente
> que está activada (por ejemplo, removiendo este aviso o creando una regla
> `.mdc` que la referencie).

---

## Objetivo (cuando se active)

Permitir la generación masiva de TRs desde HUs y la ejecución de TRs en forma
**autónoma y paralela** mediante subagentes (`mcp_task`).

---

## Arquitectura propuesta

### 1. Agente orquestador (padre)

- Lista HUs en `docs/03-historias-usuario/` (por carpeta o filtro).
- Decide qué HUs procesar (todas, subconjunto, por prioridad).
- Lanza subagentes en paralelo o en serie según dependencias.

### 2. Subagentes para generar TRs (paralelo)

Para cada HU:

1. Lee el archivo de la HU.
2. Clasifica simple vs compleja según `16-hu-simple-vs-hu-compleja.md`.
3. Ejecuta el prompt de `docs/prompts/04-Prompts-HU-a-Tareas.md`.
4. Escribe el TR en `docs/04-tareas/{subcarpeta}/TR-xxx.md`, en la misma subcarpeta que la HU.

**Paralelismo:** HUs sin dependencias entre sí → un subagente por HU.

### 3. Subagentes para ejecutar TRs (paralelo con restricciones)

Para cada TR:

1. Lee el archivo TR.
2. Ejecuta el prompt de `docs/prompts/05-Ejecucion-de-una-TR.md`.
3. Implementa DB, backend, frontend, tests según el plan.

**Dependencias:**

- Dentro de una HU: DB → Backend → Frontend → Tests (secuencial).
- Entre HUs distintas: si no comparten tablas/endpoints → paralelo.

---

## Comandos propuestos (cuando se active)

| Comando | Acción |
|---------|--------|
| `Generá los TRs para [carpeta/epic]` | Lanza subagentes en paralelo, uno por HU. |
| `Ejecutá los TRs [TR-001, TR-002, ...]` | Lanza subagentes; respeta dependencias o ejecuta por fases. |
| `Generá y ejecutá [epic]` | Fase 1: generar TRs; Fase 2: ejecutar TRs (con orden). |

---

## Patrón de invocación con `mcp_task`

```
subagent_type: generalPurpose
description: "Generar TR para HU-010"
prompt: |
  Lee docs/03-historias-usuario/001-Seguridad/HU-010-administracion-usuarios.md.
  Clasificala como HU simple o compleja según .cursor/rules/16-hu-simple-vs-hu-compleja.md.
  Genera el TR siguiendo .cursor/rules/13-user-story-to-task-breakdown.md
  y el prompt de docs/prompts/04-Prompts-HU-a-Tareas.md.
  Guarda el resultado en docs/04-tareas/001-Seguridad/TR-010-administracion-usuarios.md
  (misma subcarpeta que la HU).
  Devuelve un resumen de 2-3 líneas al finalizar.
```

---

## Limitaciones conocidas

| Aspecto | Detalle |
|---------|---------|
| Contexto | Cada subagente arranca sin contexto del padre; todo debe ir en el prompt. |
| Concurrencia | Evitar que varios subagentes editen el mismo archivo. |
| Dependencias | Modelar explícitamente en el TR (ej. "Dependencias: T1, T2"). |
| Costo | Más subagentes = más uso de modelo. |
| Resultado | El padre no ve el detalle; pedir resumen breve en el prompt. |

---

## Cómo activar esta regla

1. Remover o ajustar el aviso de "NO ACTIVA" al inicio.
2. Crear una regla `.mdc` que referencie este documento y defina cuándo aplica.
3. Verificar que existan `docs/prompts/04-Prompts-HU-a-Tareas.md` y `05-Ejecucion-de-una-TR.md`.
4. Las rutas siguen `docs/04-tareas/{subcarpeta}/` según la subcarpeta de la HU (ver regla 16).
