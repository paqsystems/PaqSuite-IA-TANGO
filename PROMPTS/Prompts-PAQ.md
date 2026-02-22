# PROMPTS RELEVANTES

## Prompt 1 - Definiciones de contexto y normativas para el back-end

ubicate en el rol de experimentado programador de Php-Laravel, con dominio de bases de datos SQL de diversas plataformas.
Necesitamos configurar todos los archivos de contexto y normativas para la programación del backend y el diseño de la base de datos para el sistema de partes de atención para consultorías y empresas de servicios detalladas en el contexto general.
es imprescindible para ello:
1) Considerar las buenas practicas de programación.
2) Contemplar las 4 normas formales de diseño de base de datos
3) Prever todos los esquemas de seguridad imprescindibles para el acceso a la aplicación
4) Idem 3) con respecto a la definición de las API, impidiendo hackeos e introducción de código malicioso


## Prompt 2 - Ajustes al diseño de la información

Importante: si durante la implementación detectas decisiones abiertas (reglas de negocio), NO las resuelvas; dejalas como TODO en documentación, sin código.

Objetivo: aplicar SOLO ajustes de diseño/modelo al dominio “Tipos de tarea” del MVP Sistema de Partes, sin implementar aún reglas completas ni cambiar endpoints/UI salvo lo mínimo para reflejar el modelo.

Cambios requeridos (SOLO diseño/modelo):
1) En la entidad/tabla TaskType (TipoDeTarea), agregar dos campos booleanos:
   - isGeneric (generico)
   - isDefault (porDefecto)
   Definir su significado en comentarios/documentación del modelo (sin imponer aún validaciones complejas).

2) Incorporar una relación explícita para asignar tipos de tarea NO genéricos a clientes:
   - Nueva tabla de asociación ClientTaskType (ClienteTipoTarea)
   - Campos mínimos: clientId, taskTypeId
   - (Opcional) unique constraint (clientId, taskTypeId) si ya usas migraciones/constraints. Si no, solo documentarlo.

Alcance / restricciones:
- NO implementes todavía la regla “solo un TaskType puede ser porDefecto en todo el sistema”.
- NO implementes todavía la regla de visibilidad “al crear tarea mostrar genéricos + asociados al cliente”.
- NO agregues endpoints nuevos ni cambies rutas.
- NO agregues lógica de negocio ni validaciones más allá de lo estrictamente necesario para persistir los nuevos campos y la relación.
- NO cambies la UI.
- SOLO actualizar: modelo de datos, migraciones (si existen), entidades/ORM, y documentación de arquitectura/specs donde corresponda.

Archivos a actualizar:
- Modelo/entidades (TaskType y la nueva relación ClientTaskType)
- Migraciones / schema
- /architecture/api-to-data-mapping.md (agregar mención de isGeneric/isDefault y ClientTaskType)
- Si existe documentación de dominio, agregar una nota breve con la definición semántica de ambos flags.

Entrega esperada:
- Cambios de código y migraciones compilables
- Documentación mínima actualizada (sin reglas de negocio todavía)


## Prompt 3 - Definiciones de contexto y normativas para el front-end

nos vamos a abocar ahora a definir todas las especificaciones y normativas para el Front end. para eso, te pido te coloques en el rol de un analista programador senior en entorno front-end web, especializado en herramientas de javascript y typescript, como react, que es la herramienta que vamos a usar específicamente. 
te pido me elabores todas las consideraciones para incorporar, conservando las buenas prácticas de programación. 
de mi parte, solicito incluir estas consideraciones : 
- que todos los controles usen la propiedad "data-testid", para utilizar en el testing TDD con playwright 
- que genere por separado html, css y js 
- que sea multilingual : para esto, pensar una clase que reciba un texto, y según una variable de entorno que se define previamente, devolverlo en el idioma seleccionado. provisoriamente, retornará el mismo texto que recibe, hasta que decidamos qué herramienta o extensión utilizaremos para las traducciones. esto debe impactar en cualquier texto que se exponga al usuario : captions, labels, tooltips, nulltext, message, etc.

## Prompt 4 - Evitar duplicidad de código

necesito que agregues en un archivo de contexto macro esta consigna : "en toda la programación, tanto back-end como front-end, evitar la duplicidad de código. generar métodos o funciones apropiados para su reutilización, y si se detecta que un código se puede incorporar a un método existente agregando algún/os parámetro/s y modificando la codificación del mismo, proponermelo antes de realizarlo"

## Prompt 5 - Ajustes al contexto de Front-End

a) con respecto a toda la definición del contexto de front-end, te pido verifiques, hayas considerado que la programación se estructure separando el css del html y del js.
b) te pido también verificarr y corregir si es necesaria la documentación, para considerar que todos los controles usen la propiedad "data-testid", para utilizar en el testing TDD con playwright, como así también contemplar siempre la accesibilidad, también para optimizaciones de testing
c) te modifiqué el archivo 03-i18n-and-testid.mdc en función a algo que ya había analizado y definido por chatgpt en forma más profunda. te pido que lo revises, lo traduzcas al español, y amplíes todo lo que consideres necesario para optimizar tu contexto, respetando la consigna acá definida

## Prompt 6 - Generar historias de usuario

Necesito que generes y escribas (o sobreescribas) el archivo: docs/historias-y-tickets.md

Objetivo:
- Crear un catálogo lo más completo posible de historias de usuario para el MVP web de consultoría/empresa de servicios:
  - Registro de tareas por empleado (con cliente, tipo de tarea, duración, fecha/hora, descripción, etc.)
  - Informes y tableros básicos
  - Gestión de clientes y tipos de tarea
  - Control y supervisión (aprobaciones/validaciones cuando corresponda)
- Debes clasificar CADA historia como MUST-HAVE o SHOULD-HAVE (para este entregable del master).
- Considera 3 tipos de usuario (roles):
  1) Cliente
  2) Empleado
  3) Empleado Supervisor

Contexto funcional mínimo (no lo ignores):
- “Tipo de tarea” (antes llamado “Proyecto”) tiene:
  - booleano “por defecto”
  - booleano “genérico”
  - Solo puede existir un tipo de tarea “por defecto” (validar/asegurar regla).
  - Los tipos “genéricos” aplican a cualquier cliente.
  - Los tipos NO genéricos deben asociarse a clientes para aparecer como opción al cargar una tarea.
- Cliente tiene atributo “Tipo de Cliente” (para informes y segmentación).

Requisitos del documento (estructura obligatoria):
1) Introducción breve (qué cubre el documento).
2) Supuestos / definiciones (roles, entidades principales: tarea, cliente, tipo de tarea, usuario, etc.).
3) Secciones por “épicas” (ejemplos: Autenticación/Acceso, Gestión de Clientes, Gestión de Tipos de Tarea, Registro de Tareas, Supervisión/Aprobación, Informes, Auditoría/Historial, Notificaciones, Administración/Configuración, Seguridad/Permisos, Integraciones/Exportación, etc.). Puedes crear más épicas si hace falta.
4) Dentro de cada épica:
   - Lista numerada de historias en formato:
     - ID: HU-XXX
     - Título
     - Rol (Cliente / Empleado / Supervisor)
     - Clasificación: MUST-HAVE o SHOULD-HAVE
     - Historia: “Como <rol> quiero <objetivo> para <beneficio>”
     - Criterios de aceptación (Gherkin o bullets claros)
     - Notas de reglas de negocio / validaciones (si aplica)
     - Dependencias (si aplica)
5) Al final:
   - Una tabla resumen (HU-XXX | Épica | Rol | MUST/SHOULD | Breve descripción)
   - Un apartado “Tickets técnicos derivados” (ID: TK-XXX) para los MUST-HAVE como mínimo:
     - migraciones/modelos
     - endpoints/API
     - componentes UI
     - tests (unit/integration/e2e)
     - CI/CD y secretos básicos
     - logging y auditoría básica
     - seed/demo data
     Cada ticket técnico debe referenciar las HU relacionadas.

Alcance de “todas las historias posibles”:
- Quiero exhaustividad razonable para un MVP: incluye altas/bajas/modificaciones, búsquedas, filtros, listados, validaciones, permisos por rol, estados de tareas si aplica, supervisión (revisión/observación/aprobación), y reportes.
- Incluye historias “del lado empresa/app” cuando no sean del usuario final (ej.: “Como administrador/sistema quiero…”), pero mantén el mapeo al rol más cercano (Supervisor o Empleado Supervisor), o crea el rol “Sistema/Administración” SOLO si es imprescindible y explícitalo en supuestos.
- Incluye historias relacionadas con:
  - trazabilidad/auditoría mínima de cambios
  - exportación básica (CSV/Excel) si corresponde
  - manejo de errores y mensajes claros
  - accesibilidad básica y UX mínima
  - seguridad básica (sesiones, roles)
  - multi-tenant NO (asumir una sola empresa) salvo que se derive del contexto; si lo incluyes que sea SHOULD.

Prioridad (MUST vs SHOULD):
- MUST-HAVE debe cubrir el flujo E2E prioritario: registrar una tarea, asociarla a cliente y tipo de tarea, y visualizar al menos un reporte básico; además de autenticación y permisos mínimos.
- SHOULD-HAVE incluye mejoras, automatizaciones, notificaciones, más reportes, importación, integraciones, etc.

Formato:
- Todo debe estar en español.
- Usa Markdown limpio con encabezados y listas.
- No dejes placeholders tipo “TBD”.
- Si inventas supuestos para completar huecos, decláralos claramente en “Supuestos”.

Acción:
- Genera el contenido completo del archivo docs/historias-y-tickets.md con todo lo anterior.

## Prompt 7 - Verificar si existen tareas pendientes para la Entrega-1

Verificar si se cumple con todo lo que requiere la “entrega-1” en .cursor/consignas.md

## Prompt 8 - Generar archivo de tareas a partir de una historia de usuario


Actuá como ingeniero senior responsable del diseño del MVP.

Usá SOLO la regla
".cursor/rules/13-user-story-to-task-breakdown.md"
como fuente de verdad.

Tarea:
A partir de la Historia de Usuario provista,
generar el plan completo de tareas/tickets
y guardarlo como archivo Markdown.
El TR generado debe incluir al final las secciones de trazabilidad (archivos/comandos/notas/pendientes), inicialmente vacías.

Archivo:
- Ruta: docs/hu-tareas/
- Nombre: igual al nombre del HU, reemplazando "HU" por "TR".
- Si existe, regenerarlo desde cero (overwrite total).

Prohibido:
- modificar otros archivos,
- inventar features,
- omitir tests, docs o tareas de calidad.

Permitido:
- declarar supuestos explícitos.

Historia de Usuario:
---
[HU]
---

## Prompt 9 - Ejecutar una tarea específica

Implementá la TR funcional ubicada en:
"docs/tareas/[NOMBRE_DEL_TR].md"

Esta TR es la FUENTE DE VERDAD del alcance.

Reglas generales:
- Implementar estrictamente las tareas definidas en la TR.
- No inventar funcionalidades fuera del alcance.
- No modificar HU ni TR sin documentarlo.
- Respetar las reglas del proyecto y de Cursor (.cursor/rules).

Implementación:
- Backend, Frontend, Tests y Documentación según lo indicado en la TR.
- Usar el layout de carpetas definido en el proyecto.
- Mantener consistencia con TRs ya implementadas.

Tests:
- Implementar unit tests, integration tests y E2E Playwright si la TR lo indica.
- En E2E:
  - Interacciones reales del usuario.
  - Assertions con expect sobre estado visible.
  - Prohibido usar waits ciegos (waitForTimeout, sleep, etc.).
  - Usar selectores estables (data-testid, roles accesibles).

Seguridad y calidad:
- Respetar validaciones, permisos y reglas de negocio.
- No revelar información sensible en mensajes de error.
- Mantener código claro y documentado.

Cierre obligatorio (trazabilidad):
- Actualizar el mismo archivo TR agregando o completando las secciones:
  - ## Archivos creados/modificados
  - ## Comandos ejecutados
  - ## Notas y decisiones
  - ## Pendientes / follow-ups
- Listar paths relativos al repositorio, agrupados por tipo (Backend, Frontend, DB, Tests, Docs).

Restricción:
- No ejecutar tareas fuera del alcance de esta TR.

---

## Prompt 10 - TR-033 Update (correcciones tras pruebas)

Cuando se detecten errores o mejoras probando el proceso TR-033:

1. Documentarlos en un archivo con el mismo nombre de la tarea TR-033 más el agregado **"-update"** (ej.: `docs/hu-tareas/TR-033(MH)-visualización-de-lista-de-tareas-propias-update.md`).
2. Resolver en la programación cada punto documentado.
3. Cuando pasen todos los testings (automáticos y manuales) y el usuario dé confirmación, actualizar el documento TR original y eliminar el archivo "-update".

**Consignas típicas TR-033 Update (ejemplo):**
1. Los datos para filtrar no están bien alineados.
2. Se repiten los títulos "Clientes" y "Tipo de tarea".
3. En la lista de Clientes: considerar la opción "Todos".
4. Si se elige en cliente la opción "Todos", que en la lista de tipo de tareas aparezca todos los registros (genéricos y no genéricos).
5. En la lista de tipos de tareas: considerar la opción "Todos".
6. Si el usuario es de nivel supervisor, agregar el filtro "Empleado".
7. Si aparece el filtro "Empleado", que también admita la opción "Todos".

---

## Prompt 11 - Generar nueva historia de usuario (menú lateral de navegación)

**Instrucción utilizada (2026-02-07):**

Quiero que generes una nueva historia de usuario, con el numero 056, de tipo SH, que reubique los botones para invocar cada proceso en la pantalla de dashboard, como opciones de menú en la parte lateral izquierda. Comentame si necesito que te explicite ahora o más adelante, el orden de presentación que desearia.

**Acción del agente:**
- Crear HU-056(SH) en `docs/hu-historias/HU-056(SH)-menú-lateral-de-navegación.md`.
- Épica: Dashboard / Navegación. Rol: Empleado / Empleado Supervisor / Cliente.
- Criterios: menú lateral fijo izquierdo; opciones actuales del dashboard reubicadas como ítems del menú; visibilidad según rol; menú colapsable/responsive; estado activo; dashboard sin bloque de botones; data-testid para E2E.
- Incluir en la HU una nota: el orden de presentación de los ítems del menú puede definirse en refinamiento o más adelante; si el usuario lo indica después, se documenta en la TR o en criterios refinados.
- Documentar en `.cursor/Docs/HU-056(SH)-menú-lateral-de-navegación.md`.

**Respuesta sobre el orden:** No es necesario explicitar ahora el orden; puede definirse más adelante en refinamiento o en la TR derivada (TR-056). Si se define después, se incorpora como criterio de aceptación explícito.

---

## Prompt 12 - Migración SQL Server → MySQL

### Fecha
2026-02-11

### Instrucción utilizada

```
haz un plan y documentalo en docs/migracion-mssql-a-mysql.md, para transformar el proyecto para que trabaje sobre una base my sql. sería con la conectividad que figura en el mcp de MySql, recordando que hay que abrir el tunel ssh para poder conectarse.
```

### Contexto

El proyecto estaba utilizando SQL Server como motor de base de datos y se requería migrar a MySQL utilizando la configuración existente en el MCP, que requiere un túnel SSH para conectarse al servidor remoto.

### Acción del agente

1. **Análisis y planificación:**
   - Revisión de configuración actual (SQL Server)
   - Identificación de código específico de SQL Server (migraciones, seeders, tests)
   - Análisis de configuración MCP MySQL existente
   - Creación de plan detallado de migración

2. **Documentación del plan:**
   - Creación de `docs/migracion-mssql-a-mysql.md` con:
     - Contexto y objetivos
     - Configuración del túnel SSH (Windows PowerShell y PuTTY)
     - Lista completa de cambios requeridos
     - Diferencias SQL Server vs MySQL
     - Proceso paso a paso
     - Checklist de verificación
     - Troubleshooting

3. **Implementación:**
   - Actualización de configuración (`.env`, `mcp/mysql-toolbox/tools.yaml`)
   - Adaptación de migraciones específicas de SQL Server
   - Actualización de seeders (reemplazo de `GETDATE()` por `now()`)
   - Actualización de tests (reemplazo de `GETDATE()` por `now()`)
   - Actualización de documentación (`deploy-ci-cd.md`, nueva regla MySQL)

4. **Ejecución:**
   - Establecimiento de túnel SSH con datos proporcionados por el usuario
   - Ejecución de migraciones (`migrate:fresh`)
   - Ejecución de seeders (`db:seed`)
   - Verificación de estructura y datos

### Resultado

- Plan completo documentado en `docs/migracion-mssql-a-mysql.md`
- Migración exitosa ejecutada
- 11 tablas creadas correctamente
- Datos iniciales insertados
- Código adaptado para MySQL
- Documentación actualizada

### Archivos clave generados/modificados

- `docs/migracion-mssql-a-mysql.md` - Documentación completa de migración
- `backend/.env` - Configuración MySQL
- Migraciones, seeders y tests adaptados
- Documentación de despliegue actualizada
- Nueva regla de formato de fechas MySQL

### Referencias
- `docs/migracion-mssql-a-mysql.md` - Plan y documentación completa

