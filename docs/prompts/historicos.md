# Prompts Históricos – Consolidado para Revisión

Este archivo reúne el contenido de `prompts.md` (raíz) y `PROMPTS/Prompts-PAQ.md` para que puedas revisar y decidir qué rescatar antes de eliminar los archivos originales.

---

# PARTE 1: prompts.md (raíz del proyecto)

## Prompts Relevantes - Proyecto Final MVP

Este documento registra los prompts más relevantes utilizados durante la creación del proyecto, organizados por sección funcional.

---

## Producto

### Prompt 1: Definición del Contexto y Alcance del MVP

**Prompt utilizado:**
```
Definir y alinear el contexto del proyecto, el alcance del MVP y los artefactos de documentación requeridos, corrigiendo desalineaciones entre lo generado por el IDE y el objetivo real del sistema.

El proyecto es un MVP web para consultorías y empresas de servicios que permite a los empleados registrar las tareas realizadas diariamente, asociándolas a clientes y tipos de tarea, con el objetivo de analizar la dedicación y facilitar la gestión operativa y comercial.
```

**Herramienta:** ChatGPT + Cursor IDE

**Resultado:**
- Definición clara del producto como sistema de registro de tareas para consultorías
- Revisión crítica de archivos generados automáticamente por el IDE
- Propuesta y redacción de archivos de contexto: `PROJECT_CONTEXT.md`, `AGENTS.md`
- Reescritura de documentación en `/docs` (producto, historias, arquitectura, modelo de datos, testing y deploy)

**Ajustes humanos:**
- Se descartó el enfoque inicial orientado a integraciones (Jira, MCP, Playwright)
- Se redujo el alcance al flujo E2E mínimo necesario para el MVP
- Se priorizó simplicidad y coherencia por sobre sobre-ingeniería
- Se validó que toda la documentación reflejara un único producto consistente


---

## Arquitectura

### Prompt 2: Definición de Arquitectura y Stack Tecnológico

**Prompt utilizado:**
```
Definir la arquitectura del sistema MVP con las siguientes características:
- Arquitectura web clásica de tres capas (Frontend, Backend, Base de Datos)
- Backend: Laravel (PHP) con Sanctum para autenticación
- Frontend: React (TypeScript)
- Base de datos: SQL Server / PostgreSQL / MySQL
- API REST con formato de respuesta estándar (envelope)
- No usar microservicios, colas ni eventos
- Priorizar claridad sobre escalabilidad futura
```

**Herramienta:** Cursor IDE

**Resultado:**
- Documento `docs/arquitectura.md` con visión general de componentes
- Decisiones clave documentadas (no microservicios, no colas)
- Especificación de comunicación Frontend-Backend vía API REST

**Ajustes humanos:**
- Se mantuvo la arquitectura simple y clara
- Se documentaron las decisiones de diseño explícitamente

---

## Modelo de Datos

### Prompt 3: Ajustes al Diseño de Tipos de Tarea

**Prompt utilizado:**
```
Aplicar SOLO ajustes de diseño/modelo al dominio "Tipos de tarea" del MVP Sistema de Partes, sin implementar aún reglas completas ni cambiar endpoints/UI salvo lo mínimo para reflejar el modelo.

Cambios requeridos (SOLO diseño/modelo):
1) En la entidad/tabla TaskType (TipoDeTarea), agregar dos campos booleanos:
   - isGeneric (generico)
   - isDefault (porDefecto)
   Definir su significado en comentarios/documentación del modelo (sin imponer aún validaciones complejas).

2) Incorporar una relación explícita para asignar tipos de tarea NO genéricos a clientes:
   - Nueva tabla de asociación ClientTaskType (ClienteTipoTarea)
   - Campos mínimos: clientId, taskTypeId
```

**Herramienta:** Cursor IDE

**Resultado:**
- Modelo `TipoTarea` actualizado con campos `is_generico` e `is_default`
- Nueva tabla pivot `ClienteTipoTarea` para asociación muchos-a-muchos
- Migraciones y modelos actualizados
- Documentación de arquitectura actualizada

**Ajustes humanos:**
- Se documentaron las reglas de negocio como TODOs (sin implementar aún)
- Se mantuvo el alcance solo en diseño/modelo, sin lógica de negocio

**Referencia:** `PROMPTS/Prompts-PAQ.md` - Prompt 2

---

## Historias de Usuario

### Prompt 4: Generación Completa de Historias de Usuario

**Prompt utilizado:**
```
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

Requisitos del documento (estructura obligatoria):
1) Introducción breve (qué cubre el documento).
2) Supuestos / definiciones (roles, entidades principales: tarea, cliente, tipo de tarea, usuario, etc.).
3) Secciones por "épicas" (Autenticación/Acceso, Gestión de Clientes, Gestión de Tipos de Tarea, Registro de Tareas, Supervisión/Aprobación, Informes, etc.).
4) Dentro de cada épica:
   - Lista numerada de historias en formato:
     - ID: HU-XXX
     - Título
     - Rol (Cliente / Empleado / Supervisor)
     - Clasificación: MUST-HAVE o SHOULD-HAVE
     - Historia: "Como <rol> quiero <objetivo> para <beneficio>"
     - Criterios de aceptación (Gherkin o bullets claros)
     - Notas de reglas de negocio / validaciones (si aplica)
     - Dependencias (si aplica)
5) Al final:
   - Una tabla resumen (HU-XXX | Épica | Rol | MUST/SHOULD | Breve descripción)
   - Un apartado "Tickets técnicos derivados" (ID: TK-XXX) para los MUST-HAVE como mínimo
```

**Herramienta:** Cursor IDE (Auto)

**Resultado:**
- Documento completo `docs/historias-y-tickets.md` con 55 historias de usuario
- 10 épicas funcionales organizadas
- 25 historias MUST-HAVE y 30 SHOULD-HAVE
- 33 tickets técnicos derivados (TK-001 a TK-033)
- Tabla resumen completa
- Criterios de aceptación detallados para cada historia

**Ajustes humanos:**
- Se validó la clasificación MUST-HAVE vs SHOULD-HAVE según el flujo E2E prioritario
- Se ajustaron algunas historias para alinearlas mejor con el alcance del MVP
- Se completaron las historias en dos pasos: primero login/configuraciones/ABM/carga, luego proceso masivo/informes/dashboard

**Referencia:** `PROMPTS/Prompts-PAQ.md` - Prompt 6

---

## API / Endpoints

### Prompt 5: Generación de Especificaciones de Endpoints

**Prompt utilizado:**
```
Generar especificaciones completas de endpoints para el sistema de registro de tareas. Debe incluir:

1. Endpoints de autenticación (login, logout)
2. Endpoints de gestión de clientes (CRUD completo + asignación de tipos de tarea)
3. Endpoints de gestión de tipos de cliente (CRUD completo)
4. Endpoints de gestión de asistentes (CRUD completo)
5. Endpoints de gestión de tipos de tarea (CRUD completo)
6. Endpoints de registro de tareas (CRUD completo + tipos disponibles)
7. Endpoints de proceso masivo (listar, procesar)
8. Endpoints de informes (detalle, por asistente, por cliente, por tipo, por fecha, exportar)
9. Endpoints de dashboard (resumen, por cliente, por asistente)

Cada especificación debe incluir:
- Método HTTP y ruta
- Autenticación requerida
- Request (headers, body, parámetros)
- Response (éxito y errores)
- Validaciones
- Ejemplos de uso
- Códigos de error aplicables

Formato: Archivos Markdown en specs/endpoints/
```

**Herramienta:** Cursor IDE (Auto)

**Resultado:**
- 41 especificaciones de endpoints completas en `specs/endpoints/`
- Cada endpoint documentado con formato estándar
- Referencias a códigos de error y reglas de validación
- Ejemplos de uso en cURL y JavaScript

**Ajustes humanos:**
- Se validó que todos los endpoints sigan el formato de respuesta estándar (envelope)
- Se aseguró consistencia en la documentación de permisos y filtros automáticos
- Se completaron los endpoints en lotes según prioridad (críticos primero)

**Referencia:** `docs/REVISION-ESPECIFICACIONES.md` (ahora eliminado, pero el trabajo se completó)

---

## Reglas de Negocio y Validaciones

### Prompt 6: Generación de Reglas de Negocio Específicas

**Prompt utilizado:**
```
Crear documento de reglas de negocio específicas del dominio que deben aplicarse en el sistema, más allá de las validaciones básicas de formato y tipo de datos.

Debe incluir:
- Regla de tipo de tarea por defecto (solo uno puede tener is_default = true)
- Regla de tipos genéricos vs específicos
- Regla de cliente y tipos de tarea (debe tener al menos un tipo genérico o asignado)
- Regla de tarea cerrada (no se puede modificar/eliminar)
- Regla de integridad referencial (no eliminar si tiene referencias)
- Regla de permisos por rol (empleado solo sus tareas, supervisor todas)
- Regla de filtros automáticos según tipo de usuario
- Regla de duración en tramos de 15 minutos
- Regla de fecha futura (advertencia)
- Regla de observación obligatoria

Cada regla debe incluir:
- Descripción
- Implementación (código de ejemplo)
- Código de error asociado
- Validación
```

**Herramienta:** Cursor IDE (Auto)

**Resultado:**
- Documento `specs/rules/business-rules.md` con 10 reglas de negocio específicas
- Cada regla documentada con implementación, códigos de error y validaciones
- Orden de aplicación de reglas documentado

**Ajustes humanos:**
- Se validó que todas las reglas estén alineadas con las historias de usuario
- Se aseguró que los códigos de error sean consistentes con `specs/errors/domain-error-codes.md`

---

## Backend - Modelos

### Prompt 7: Creación de Modelos Backend Faltantes

**Prompt utilizado:**
```
Crear los modelos backend faltantes según la especificación:

1. Usuario (Empleado/Asistente)
   - Tabla: PQ_PARTES_usuario
   - Campos: id, code, nombre, email, password_hash, supervisor, activo, inhabilitado
   - Relaciones: hasMany(RegistroTarea)
   - Extiende Authenticatable (Sanctum)

2. RegistroTarea
   - Tabla: PQ_PARTES_registro_tarea
   - Campos: id, usuario_id, cliente_id, tipo_tarea_id, fecha, duracion_minutos, sin_cargo, presencial, observacion, cerrado
   - Relaciones: belongsTo(Usuario), belongsTo(Cliente), belongsTo(TipoTarea)
   - Scopes: abiertas, cerradas, delUsuario, delCliente, enRangoFechas

Usar Eloquent de Laravel, seguir el formato de los modelos existentes (Cliente, TipoTarea, etc.).
```

**Herramienta:** Cursor IDE (Auto)

**Resultado:**
- Modelo `backend/app/Models/Usuario.php` con autenticación Sanctum
- Modelo `backend/app/Models/RegistroTarea.php` con relaciones y scopes
- Métodos helper (isHabilitado, isSupervisor, isCerrada, getDuracionHorasAttribute)
- Scopes útiles para consultas

**Ajustes humanos:**
- Se validó que los modelos sigan las convenciones de Laravel
- Se aseguró que las relaciones estén correctamente definidas
- Se agregaron métodos helper para facilitar el uso

---

## Frontend

### Prompt 8: Definiciones de Contexto y Normativas para Frontend

**Prompt utilizado:**
```
Definir todas las especificaciones y normativas para el Front end. Colocarse en el rol de analista programador senior en entorno front-end web, especializado en React y TypeScript.

Consideraciones a incluir:
- Todos los controles usen la propiedad "data-testid" para testing TDD con Playwright
- Separar HTML, CSS y JS
- Multilingual: clase que reciba un texto y según variable de entorno devuelva el texto traducido. Provisoriamente retorna el mismo texto. Debe impactar en cualquier texto expuesto al usuario (captions, labels, tooltips, nulltext, messages, etc.)
- Buenas prácticas de programación
- Estructura de carpetas por features
- UI Layer Wrappers (componentes base reutilizables)
- Accesibilidad (A11y)
```

**Herramienta:** ChatGPT + Cursor IDE

**Resultado:**
- Documento `docs/frontend/frontend-specifications.md` con especificaciones completas
- Documento `docs/frontend/i18n.md` con reglas de internacionalización
- Documento `docs/frontend/ui-layer-wrappers.md` con especificación de componentes base
- Documento `docs/frontend/features/features-structure.md` con estructura de features
- Componentes UI base creados: Button, TextField, DataTable, Modal

**Ajustes humanos:**
- Se validó que todas las especificaciones sean consistentes
- Se aseguró que los componentes base tengan testId y soporte i18n
- Se documentó la estructura de features para organizar el desarrollo

**Referencia:** `PROMPTS/Prompts-PAQ.md` - Prompt 3 y Prompt 5

---

## Especificaciones de Modelos Backend

### Prompt 9: Creación de Especificaciones de Modelos

**Prompt utilizado:**
```
Crear especificaciones detalladas de modelos backend en specs/models/ para cada modelo:

1. usuario-model.md
2. registro-tarea-model.md
3. cliente-model.md
4. tipo-cliente-model.md
5. tipo-tarea-model.md
6. cliente-tipo-tarea-model.md

Cada especificación debe incluir:
- Información general (tabla, modelo, descripción)
- Campos con tipos y restricciones
- Índices
- Relaciones
- Validaciones
- Scopes (si aplica)
- Métodos helper (si aplica)
- Reglas de negocio aplicables
```

**Herramienta:** Cursor IDE (Auto)

**Resultado:**
- 6 especificaciones completas de modelos en `specs/models/`
- Cada especificación documentada con todos los detalles técnicos
- Referencias cruzadas con reglas de negocio y validaciones

**Ajustes humanos:**
- Se validó que las especificaciones coincidan con los modelos implementados
- Se aseguró consistencia en la documentación

---

## Manual del Programador

### Prompt 13: Revisión y Actualización del Manual del Programador

**Prompt utilizado:**
```
podrías revisar si es necesario actualizar el archivo "manual-del-programador.md". Recuerda que su objetivo es que un nuevo programador con conocimientos de las herramientas que se utilizan pueda entender el objetivo funcional y estructura técnica del proyecto sin necesidad de explicación humana.
```

**Herramienta:** Cursor IDE

**Resultado:**
- Revisión completa del documento `_MANUAL-PROGRAMADOR.MD`
- Corrección de referencias a archivos inexistentes o incorrectos (`_PROJECT_CONTEXT.md`, reglas i18n)
- Actualización del flujo E2E para alinear con README (5 pasos)
- Incorporación de `docs/README.md` y `backend/README.md` en la Ruta de Lectura
- Añadido comando `npm run test:all` para cierre de tareas en frontend
- Actualizada estructura del proyecto, tabla de referencias y convención camelCase
- Reconstrucción de `docs/README.md` y `backend/README.md` (habían sido eliminados)
- Versión actualizada a 1.4

**Ajustes humanos:**
- Validación de que el manual permita autonomía total a un programador nuevo
- Verificación de que todas las referencias apunten a archivos existentes

**Referencia:** `_MANUAL-PROGRAMADOR.MD` (v1.3, v1.4)

---

## Notas sobre el Uso de IA

### Estrategia General

1. **IA como Asistente:** La IA se utilizó como asistente para generar código y documentación, pero todas las decisiones de diseño y ajustes fueron humanos.

2. **Revisión Crítica:** Todos los artefactos generados por IA fueron revisados críticamente y ajustados según el contexto del proyecto y las consignas del MVP.

3. **Iteración:** El proceso fue iterativo: se generaba un artefacto, se revisaba, se ajustaba, y se refinaba hasta alcanzar la calidad requerida.

4. **Trazabilidad:** Se mantuvo registro de los prompts utilizados y los ajustes humanos realizados.

### Herramientas Utilizadas

- **Cursor IDE:** Generación de código, especificaciones y documentación técnica
- **ChatGPT:** Asistencia en diseño, revisión y refinamiento de artefactos
- **Git:** Control de versiones y trazabilidad de cambios

### Principios Aplicados

- **Simplicidad sobre sofisticación:** Se priorizó claridad y mantenibilidad
- **No sobre-ingeniería:** Se evitó agregar complejidad innecesaria
- **Valor completo:** Se enfocó en entregar un flujo E2E funcional
- **Documentación clara:** Todos los artefactos están documentados y son comprensibles

---

## Formato de Fechas

### Prompt 10: Ajuste de Formato de Fechas en TR-028

**Prompt utilizado:**
```
analizando el archivo resultante hago una observación importante con respecto al formato de fechas. Para todo el tratamiento de la base de datos, quedamos en normalizar el formato en YMD, pero para interacción con el usuario (FrontEnd), SIEMPRE debe ser DMY.. puedes ajustar este documento, y las reglas que consideres necesarias?
```

**Herramienta:** Cursor IDE

**Resultado:**
- Actualización del TR-028 para reflejar formato DMY en frontend (interacción con usuario) y YMD en backend/BD
- Reglas de negocio actualizadas con especificación de formatos
- Criterios de aceptación ajustados
- Plan de tareas actualizado para incluir conversión DMY ↔ YMD
- Estrategia de tests actualizada para verificar conversión

**Ajustes humanos:**
- Se especificó claramente que frontend muestra/captura DMY pero convierte a YMD antes de enviar al API
- Se documentó la responsabilidad del frontend en la conversión de formatos

**Referencia:** `docs/04-tareas/TR-028(MH)-carga-de-tarea-diaria.md`

---

### Prompt 11: Refinamiento de Manejo de Fechas - Formato Interno YMD

**Prompt utilizado:**
```
consulta, los controles que manejan fecha, no tienen la posibilidad de formatear la presentación? quizás convenga que internamente siempre se maneje las fechas como YMD, sólo en el control de fecha que se despliega al usuario para visualizar o editar se cambia el formato.
```

**Herramienta:** Cursor IDE

**Resultado:**
- Refinamiento del TR-028 para reflejar que los componentes de fecha manejan internamente YMD
- Solo la visualización se formatea a DMY usando funciones de formato o configuración del componente
- Eliminación de conversiones innecesarias en el código
- Actualización de plan de tareas para usar formateo de visualización en lugar de conversión
- Actualización de estrategia de tests para verificar formateo de visualización

**Ajustes humanos:**
- Se simplificó la implementación: no se requiere conversión, solo formateo de visualización
- Se documentó que los componentes de fecha (`<input type="date">` o librerías) manejan internamente YMD
- Se recomendaron opciones de componentes de fecha (HTML5 nativo, react-datepicker, date-fns/dayjs)

**Referencia:** `docs/04-tareas/TR-028(MH)-carga-de-tarea-diaria.md`

---

## Generación Masiva de TRs

### Prompt 12: Generación Masiva de TRs desde HU (HU-029 a HU-038)

**Prompt utilizado:**
```
Aplicá el flujo definido para conversión de Historias de Usuario a tareas
sobre las Historias de Usuario numeradas del 29 al 38 inclusive,
ubicadas en la carpeta docs/hu-historias/.

Procesá únicamente archivos cuyo nombre o contenido identifique
claramente a las HU 29 a 38.

Condiciones obligatorias:
- Todas las HU del rango indicado son MUST-HAVE.
- Determiná automáticamente si cada HU es SIMPLE o COMPLEJA,
  según las reglas ya definidas en el proyecto.
- En caso de duda razonable sobre si una HU debe tratarse como SIMPLE o COMPLEJA:
  - Adoptá un criterio conservador y tratala como HU COMPLEJA.
  - Documentá explícitamente la duda y el motivo de la ambigüedad.
- Generá únicamente las tareas necesarias para implementar cada HU.
- NO ejecutes tareas.
- NO escribas código.
- NO inicialices servidores.
- NO modifiques otros archivos fuera del indicado.

Para cada HU:
1. Indicá si fue tratada como HU SIMPLE o HU COMPLEJA.
2. Si es COMPLEJA, descomponela primero en sub-historias lógicas.
3. Generá las tareas técnicas correspondientes.
4. Clasificá cada tarea por capa:
   - Backend
   - Frontend
   - Base de Datos
   - QA / Testing
5. Indicá dependencias entre tareas si las hubiera.
6. Verificá coherencia con el MVP y los entregables definidos.
7. Marcá las HU tratadas como COMPLEJAS por criterio conservador
   con la etiqueta: [REVISAR_SIMPLICIDAD]

Registrá el resultado de forma ordenada en:
docs/tareas-generadas-hu-29-a-38.md

Este trabajo es exclusivamente de análisis y planificación.
```

**Herramienta:** Cursor IDE (Claude)

**Resultado:**
- Procesamiento masivo de 10 Historias de Usuario (HU-029 a HU-038)
- Determinación automática de complejidad (SIMPLE vs COMPLEJA) para cada HU
- Generación de TRs completos siguiendo la estructura estándar del proyecto
- Documentación consolidada en archivo de resumen

**Ajustes humanos:**
- Revisión de clasificación SIMPLE/COMPLEJA según criterios del proyecto
- Validación de coherencia con TRs ya implementadas (TR-028)
- Verificación de dependencias entre HU y TRs

**Referencia:** `PROMPTS/07 - Generaciòn HU a TR masivo.md`

---

**Última actualización:** 2025-02-15

---

# PARTE 2: PROMPTS/Prompts-PAQ.md

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

Objetivo: aplicar SOLO ajustes de diseño/modelo al dominio "Tipos de tarea" del MVP Sistema de Partes, sin implementar aún reglas completas ni cambiar endpoints/UI salvo lo mínimo para reflejar el modelo.

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
- NO implementes todavía la regla "solo un TaskType puede ser porDefecto en todo el sistema".
- NO implementes todavía la regla de visibilidad "al crear tarea mostrar genéricos + asociados al cliente".
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

a) con respecto a toda la definición de front-end, te pido verifiques, hayas considerado que la programación se estructure separando el css del html y del js.
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
- "Tipo de tarea" (antes llamado "Proyecto") tiene:
  - booleano "por defecto"
  - booleano "genérico"
  - Solo puede existir un tipo de tarea "por defecto" (validar/asegurar regla).
  - Los tipos "genéricos" aplican a cualquier cliente.
  - Los tipos NO genéricos deben asociarse a clientes para aparecer como opción al cargar una tarea.
- Cliente tiene atributo "Tipo de Cliente" (para informes y segmentación).

Requisitos del documento (estructura obligatoria):
1) Introducción breve (qué cubre el documento).
2) Supuestos / definiciones (roles, entidades principales: tarea, cliente, tipo de tarea, usuario, etc.).
3) Secciones por "épicas" (ejemplos: Autenticación/Acceso, Gestión de Clientes, Gestión de Tipos de Tarea, Registro de Tareas, Supervisión/Aprobación, Informes, Auditoría/Historial, Notificaciones, Administración/Configuración, Seguridad/Permisos, Integraciones/Exportación, etc.). Puedes crear más épicas si hace falta.
4) Dentro de cada épica:
   - Lista numerada de historias en formato:
     - ID: HU-XXX
     - Título
     - Rol (Cliente / Empleado / Supervisor)
     - Clasificación: MUST-HAVE o SHOULD-HAVE
     - Historia: "Como <rol> quiero <objetivo> para <beneficio>"
     - Criterios de aceptación (Gherkin o bullets claros)
     - Notas de reglas de negocio / validaciones (si aplica)
     - Dependencias (si aplica)
5) Al final:
   - Una tabla resumen (HU-XXX | Épica | Rol | MUST/SHOULD | Breve descripción)
   - Un apartado "Tickets técnicos derivados" (ID: TK-XXX) para los MUST-HAVE como mínimo:
     - migraciones/modelos
     - endpoints/API
     - componentes UI
     - tests (unit/integration/e2e)
     - CI/CD y secretos básicos
     - logging y auditoría básica
     - seed/demo data
     Cada ticket técnico debe referenciar las HU relacionadas.

Alcance de "todas las historias posibles":
- Quiero exhaustividad razonable para un MVP: incluye altas/bajas/modificaciones, búsquedas, filtros, listados, validaciones, permisos por rol, estados de tareas si aplica, supervisión (revisión/observación/aprobación), y reportes.
- Incluye historias "del lado empresa/app" cuando no sean del usuario final (ej.: "Como administrador/sistema quiero…"), pero mantén el mapeo al rol más cercano (Supervisor o Empleado Supervisor), o crea el rol "Sistema/Administración" SOLO si es imprescindible y explícitalo en supuestos.
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
- No dejes placeholders tipo "TBD".
- Si inventas supuestos para completar huecos, decláralos claramente en "Supuestos".

Acción:
- Genera el contenido completo del archivo docs/historias-y-tickets.md con todo lo anterior.

## Prompt 7 - Verificar si existen tareas pendientes para la Entrega-1

Verificar si se cumple con todo lo que requiere la "entrega-1" en .cursor/consignas.md

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
- Ruta: docs/04-tareas/
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
"docs/04-tareas/[NOMBRE_DEL_TR].md"

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

1. Documentarlos en un archivo con el mismo nombre de la tarea TR-033 más el agregado **"-update"** (ej.: `docs/04-tareas/TR-033(MH)-visualización-de-lista-de-tareas-propias-update.md`).
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
- Crear HU-056(SH) en `docs/03-historias-usuario/HU-056(SH)-menú-lateral-de-navegación.md`.
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
   - Actualización de documentación (`06-operacion/deploy-infraestructura.md`, nueva regla MySQL)

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
