# PROJECT_CONTEXT.md

## Contexto General
Este proyecto es un **MVP web** con procesos e informes que complementan al sistema Tango Gestión.

El sistema permite que empleados ingresen al sistema, elijan una empresa de las que tienen habilitados en Tango y ejecuten cualquiera de los procesos que tienen habilitados a realizar.

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
- No es un ERP , sino un complemento del mismo.
- No incluye facturación ni reportes avanzados en el MVP.

---

## Usuarios
- Empleados

---

## Flujo E2E Prioritario
Login → Selección Empresas -> (por módulo) : carga de datos de un módulo -> selección de informes de ese módulo -> actualización del dash

Todo el desarrollo debe soportar este flujo.

---

## Entidades Clave
- Usuario
- Empresa

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
- Integración y Desplegado

Las decisiones finales y ajustes son humanos.
