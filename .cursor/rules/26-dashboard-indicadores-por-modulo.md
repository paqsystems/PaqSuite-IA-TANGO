# Regla: Indicadores de Dashboard en Historias de Módulo

## Objetivo

Cada vez que se solicite generar **todas las historias de usuario** de un tema o módulo específico, la generación debe incluir la creación de **indicadores o KPIs para el dashboard de entrada**.

## Alcance

- **Cuándo aplica:** Al generar el conjunto completo de HUs de un módulo (ej. Partes de Producción, Ventas, Stock).
- **Qué incluir:** Una o más historias (o criterios de aceptación dentro de HUs existentes) que definan los indicadores del dashboard para ese módulo.

## Reglas de los indicadores

### 1. Disponibilidad

Los indicadores del módulo **solo estarán disponibles** si:

- El módulo está **disponible en la instalación** (licencia/producto contratado).
- El módulo está **habilitado para el cliente** (configuración por empresa/tenant).

Si no se cumplen ambas condiciones, el indicador no se muestra en el dashboard.

### 2. Alcance de datos por rol

| Rol | Alcance de datos |
|-----|------------------|
| **Supervisor** | Incluye **todos** los datos del módulo (equipo, área, empresa según permisos). |
| **Usuario no supervisor** | Incluye **solo** los valores correspondientes al usuario (sus registros, sus clientes, sus proyectos, etc.). |

### 3. Ubicación en el output

Al generar HUs de módulo, incluir:

- Una **HU de indicadores** (o sección equivalente) que describa:
  - Qué KPIs/indicadores se muestran.
  - Condiciones de visibilidad (módulo instalado y habilitado).
  - Reglas de filtrado por rol (supervisor vs no supervisor).
- Referencia al dashboard de entrada (`docs/design/paqsystems-main-shell-design.md`, Main Content).

## Ejemplo de criterios de aceptación

```
- Los indicadores del módulo [X] se muestran en el dashboard solo si el módulo está instalado y habilitado para la empresa.
- Si el usuario es supervisor: los indicadores muestran el total del equipo/área.
- Si el usuario no es supervisor: los indicadores muestran solo sus propios datos.
- Cada indicador incluye valor actual y variación (↑/↓) respecto al período anterior cuando aplique.
```

## Referencias

- `docs/design/paqsystems-main-shell-design.md` – Estructura del dashboard
- `docs/ui/mockups/mockup-spec-mainlayout.md` – Grid sugerida (KPIs, cards)
- `.cursor/rules/13-user-story-to-task-breakdown.md` – Descomposición HU → tareas
