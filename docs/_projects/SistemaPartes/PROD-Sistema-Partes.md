# Producto – MVP Sistema de Registro de Tareas

## Visión del Producto

### Descripción General
Sistema web para consultorías y empresas de servicios que permite a los empleados
registrar las tareas realizadas diariamente, asociándolas a clientes y tipos de tarea,
con el objetivo de analizar la dedicación y facilitar la gestión operativa y comercial.

Este producto corresponde al **MVP del proyecto final** y prioriza simplicidad,
claridad y valor completo en un flujo E2E.

---

## Propósito
- Registrar tareas diarias de forma simple y rápida.
- Obtener visibilidad sobre la dedicación a cada cliente.
- Sentar las bases para futuros módulos de análisis o facturación.

---

## Público Objetivo
- **Empleados:** Consultores, empleados administrativos, equipos de servicios profesionales que registran tareas
- **Clientes:** Empresas/clientes que pueden consultar las tareas realizadas para ellos

---

## Características Principales (MVP)

### Funcionalidades

**Para Empleados:**
- Autenticación mediante código de usuario y contraseña
- Registro de tareas diarias:
  - Fecha
  - Cliente
  - Tipo de tarea
  - Duración
- Edición y eliminación de tareas propias
- Visualización de tareas registradas
- Resumen básico de dedicación por cliente

**Para Supervisores (empleados con `supervisor = true`):**
- Todas las funcionalidades de empleados normales
- Visualización de tareas de todos los usuarios
- Creación, edición y eliminación de tareas de cualquier usuario
- Al crear una tarea, puede seleccionar el usuario propietario (lista desplegable, por defecto aparece él mismo)

**Para Clientes:**
- Registro y autenticación (si tienen acceso habilitado)
- Consulta de tareas realizadas para ellos (solo lectura)
- Visualización de resumen de dedicación recibida

---

## Flujo E2E Prioritario
Login → Registro de tarea → Visualización de resumen.

---

## Fuera de Alcance del MVP
- Facturación automática.
- Integración con sistemas externos.
- Roles avanzados más allá de supervisor (administrador, etc.).
- Reportes complejos o dashboards avanzados.

---

## Roadmap Tentativo (Post-MVP)
- Aprobación de tareas por supervisor.
- Reportes avanzados por período.
- Integración con sistemas de facturación o ERP.
- Exportación de datos.
