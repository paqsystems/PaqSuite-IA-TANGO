# Prompts Relevantes - Proyecto Final MVP

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

**Referencia:** `docs/hu-tareas/TR-028(MH)-carga-de-tarea-diaria.md`

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

**Referencia:** `docs/hu-tareas/TR-028(MH)-carga-de-tarea-diaria.md`

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

