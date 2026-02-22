> Detalla en esta sección los prompts principales utilizados durante la creación del proyecto, que justifiquen el uso de asistentes de código en todas las fases del ciclo de vida del desarrollo. Esperamos un máximo de 3 por sección, principalmente los de creación inicial o los de corrección o adición de funcionalidades que consideres más relevantes.
>
> **Referencia ampliada:** Ver `prompts.md` y `docs/ia-log.md` para prompts detallados con resultado, herramienta y ajustes humanos.


## Índice

1. [Descripción general del producto](#1-descripción-general-del-producto)
2. [Arquitectura del sistema](#2-arquitectura-del-sistema)
3. [Modelo de datos](#3-modelo-de-datos)
4. [Especificación de la API](#4-especificación-de-la-api)
5. [Historias de usuario](#5-historias-de-usuario)
6. [Tickets de trabajo](#6-tickets-de-trabajo)
7. [Pull requests](#7-pull-requests)

---

## 1. Descripción general del producto

**Prompt 1:** Definir y alinear el contexto del proyecto, el alcance del MVP y los artefactos de documentación requeridos. El proyecto es un MVP web para consultorías y empresas de servicios que permite a los empleados registrar las tareas realizadas diariamente, asociándolas a clientes y tipos de tarea. *(ChatGPT + Cursor)*

**Prompt 2:** Revisar y corregir desalineaciones entre lo generado por el IDE y el objetivo real del sistema. Reducir alcance al flujo E2E mínimo; priorizar simplicidad y coherencia por sobre sobre-ingeniería. *(Cursor)*

**Prompt 3:** Validar que toda la documentación (producto, historias, arquitectura) refleje un único producto consistente; descartar enfoques orientados a integraciones externas no necesarias. *(Revisión humana + Cursor)*

---

## 2. Arquitectura del Sistema

### **2.1. Diagrama de arquitectura:**

**Prompt 1:** Definir la arquitectura del sistema MVP: tres capas (Frontend, Backend, BD); Backend Laravel con Sanctum; Frontend React (TypeScript); API REST; no microservicios, colas ni eventos. *(Cursor)*

**Prompt 2:** Priorizar claridad sobre escalabilidad futura; documentar decisiones de diseño explícitamente. *(Cursor)*

**Prompt 3:** Generar diagrama ASCII de flujo Frontend → Backend API → Base de Datos con tecnologías. *(Cursor)*

### **2.2. Descripción de componentes principales:**

**Prompt 1:** Describir Frontend como SPA React con funciones: Login, Registro tareas, Listado; comunicación vía API REST. *(Cursor)*

**Prompt 2:** Describir Backend: API REST, autenticación, validaciones, persistencia, control de acceso por usuario. *(Cursor)*

**Prompt 3:** Describir BD relacional: entidades normalizadas, índices en FKs y fechas. *(Cursor)*

### **2.3. Descripción de alto nivel del proyecto y estructura de ficheros**

**Prompt 1:** Representar estructura del proyecto con carpetas backend/, frontend/, docs/, specs/ y propósito de cada una. *(Cursor)*

**Prompt 2:** Explicar estructura por features en frontend; capas MVC en backend. *(Cursor)*

### **2.4. Infraestructura y despliegue**

**Prompt 1:** Configurar pipeline CI/CD básico con GitHub Actions: tests backend, frontend, Swagger, E2E. *(Cursor)*

**Prompt 2:** Añadir vercel.json con rewrites para SPA (rutas /login, /dashboard sirvan index.html). *(Cursor)*

**Prompt 3:** Documentar deploy: Vercel (frontend), opciones backend (Render, Fly.io), MySQL administrado. *(Cursor)*

### **2.5. Seguridad**

**Prompt 1:** Implementar autenticación Sanctum; contraseñas con bcrypt; middleware en endpoints protegidos. *(Cursor)*

**Prompt 2:** Validar que mensajes de error de login no revelen si el usuario existe. *(Cursor)*

**Prompt 3:** Aplicar filtros automáticos por rol en consultas (empleado solo sus tareas; supervisor todas). *(Cursor)*

### **2.6. Tests**

**Prompt 1:** Configurar Playwright para tests E2E en frontend; data-testid en controles; flujo login → tarea → visualización. *(Cursor)*

**Prompt 2:** Crear tests unitarios backend (servicios Auth, Task); tests de integración (API + BD). *(Cursor)*

**Prompt 3:** Ejecutar npm run test:all en frontend al cerrar tareas; php artisan test en backend. *(Documentación)*

---

### 3. Modelo de Datos

**Prompt 1:** Aplicar ajustes al dominio Tipos de tarea: agregar is_generico e is_default en TipoTarea; crear tabla ClienteTipoTarea para asociación N:M. *(Cursor)*

**Prompt 2:** Generar diagrama Mermaid del modelo con entidades User, Usuario, Cliente, TipoCliente, TipoTarea, RegistroTarea, ClienteTipoTarea; PKs, FKs y relaciones. *(Cursor)*

**Prompt 3:** Documentar reglas: tipo por defecto (solo uno is_default=true); tipos genéricos disponibles para todos; duración en tramos de 15 min. *(Cursor)*

---

### 4. Especificación de la API

**Prompt 1:** Generar especificaciones completas de endpoints: autenticación, clientes, tipos, empleados, tareas, informes, dashboard. Formato Markdown en specs/endpoints/; método, ruta, request, response, validaciones. *(Cursor)*

**Prompt 2:** Definir formato envelope estándar de respuesta: { error, respuesta, resultado }; aplicar a todos los endpoints. *(Cursor)*

**Prompt 3:** Configurar l5-swagger; generar OpenAPI; documentar en /api/documentation. *(Cursor)*

---

### 5. Historias de Usuario

**Prompt 1:** Generar catálogo completo de historias de usuario para el MVP: registro de tareas, informes, gestión de clientes/empleados/tipos, control y supervisión. Clasificar cada una como MUST-HAVE o SHOULD-HAVE. Considerar roles: Cliente, Empleado, Empleado Supervisor. *(Cursor)*

**Prompt 2:** Estructurar por épicas (Autenticación, Gestión Clientes, Registro Tareas, Informes, Dashboard); incluir criterios de aceptación Gherkin; tabla resumen final. *(Cursor)*

**Prompt 3:** Derivar tickets técnicos (TK-001 a TK-033) de las historias MUST-HAVE; asociar cada ticket a HUs relacionadas. *(Cursor)*

---

### 6. Tickets de Trabajo

**Prompt 1:** Implementar TK-002 Endpoints de Autenticación: POST login (validar USERS, determinar Cliente/Usuario, generar token Sanctum); POST logout. *(Cursor)*

**Prompt 2:** Implementar TK-014 Componentes UI Registro Tareas: formulario con fecha, cliente, tipo tarea filtrado, duración 15 min, observación; data-testid para Playwright. *(Cursor)*

**Prompt 3:** Implementar TK-001 Migraciones: crear tablas USERS, PQ_PARTES_* con FKs e índices; seeders con datos mínimos (ADMIN, EMP001, CLI001). *(Cursor)*

---

### 7. Pull Requests

**Prompt 1:** Documentar PR Entrega 1: descripción de cambios (documentación técnica); referenciar historias y tickets; título claro. *(Redacción humana)*

**Prompt 2:** Documentar PR Entrega 2: descripción de backend, frontend, BD conectada; evidencia de flujo casi completo; URL Vercel. *(Redacción humana)*

**Prompt 3:** Generar texto para PR final: pipeline CI, vercel.json, links de repositorio y URL pública según README. *(Cursor)*