# Regla: Trazabilidad de ejecución de Tareas (TR)

## Objetivo
Cada vez que Cursor implemente una TR (TR-*.md), debe dejar evidencia clara de:
- qué archivos creó/modificó,
- qué comandos ejecutó,
- qué decisiones técnicas tomó,
- y qué quedó pendiente.

## Reglas obligatorias
1) Al finalizar la implementación de una TR, actualizar el mismo archivo TR agregando al final estas secciones (si no existen, crearlas):
   - ## Archivos creados/modificados
   - ## Comandos ejecutados
   - ## Notas y decisiones
   - ## Pendientes / follow-ups

2) En "Archivos creados/modificados", listar paths relativos al repo, agrupando por:
   - Backend
   - Frontend
   - Database/Migrations/Seeds
   - Tests
   - Docs/CI

3) En "Comandos ejecutados", listar comandos (ej: php artisan..., npm..., playwright...) y el objetivo de cada uno.

4) Si durante la implementación se decide cambiar rutas, nombres o estructura respecto de la TR original:
   - documentar el cambio en "Notas y decisiones",
   - y actualizar cualquier documentación afectada.

5) Nunca borrar información previa del TR: si se re-ejecuta una TR, agregar una subsección con timestamp o encabezado de corrida.
