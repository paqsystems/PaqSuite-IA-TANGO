# PROJECT_CONTEXT.md

## Contexto General
Este proyecto es un **MVP web** para consultorías y empresas de servicios.

El sistema permite que empleados o asistentes registren las tareas realizadas
diariamente, indicando cliente, tipo de tarea y duración, con el fin de obtener
informes de dedicación para análisis operativo y comercial.

---

## Objetivo del MVP
- Registrar tareas diarias de forma simple.
- Asociar tareas a clientes y tipos de trabajo.
- Visualizar la dedicación por usuario y cliente.
- Entregar un flujo E2E completo y funcional.

---

## Qué SÍ es este proyecto
- Un sistema de registro de tareas (time tracking simple).
- Un MVP con foco en claridad y valor completo.
- Una aplicación web con backend, frontend, tests y deploy.
- Un proyecto académico con entregables obligatorios.

---

## Qué NO es este proyecto
- No es una plataforma de automatización.
- No es un integrador de Jira, MCP, Playwright u otras herramientas.
- No es un ERP.
- No incluye facturación ni reportes avanzados en el MVP.

---

## Usuarios
- Empleado / Consultor 
- Clientes (para consultas únicamente)

---

## Flujo E2E Prioritario
Login → Registro de tarea diaria → Visualización de tareas / resumen.

Todo el desarrollo debe soportar este flujo.

---

## Entidades Clave
- Usuario
- Cliente
- Tipo de Tarea
- Registro de Tarea

---

## Principios de Diseño
- Simplicidad sobre sofisticación.
- No sobre–ingeniería.
- Validaciones claras.
- Trazabilidad del trabajo.
- Testing enfocado en el flujo principal.

---

## Alcance Técnico
- Arquitectura web simple (Frontend + Backend + DB).
- API REST.
- Autenticación básica.
- Tests unitarios, integración y al menos 1 E2E.

---

## Uso de IA
La IA se utiliza como asistente en:
- Diseño
- Generación de código base
- Testing
- Documentación

Las decisiones finales y ajustes son humanos y se registran en:
`docs/ia-log.md`
