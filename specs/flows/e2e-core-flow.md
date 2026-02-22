# Flujo E2E Core — Sistema de Registro de Tareas

## Descripción General

Este documento especifica el flujo End-to-End (E2E) prioritario del MVP: **Login → Registro de tarea → Visualización de resumen**.

Este flujo representa el valor completo que el sistema debe entregar y es el foco principal de desarrollo y testing.

---

## Objetivo del Flujo

Permitir que un empleado:
1. Se autentique en el sistema
2. Registre una tarea diaria realizada
3. Visualice un resumen de su dedicación

---

## Actores

- **Empleado/Consultor**: Usuario final que registra sus tareas diarias

---

## Flujo Principal

### Paso 1: Autenticación (Login)

**Objetivo:** El usuario se autentica en el sistema.

**Precondiciones:**
- El usuario existe en el sistema
- El usuario tiene credenciales válidas (código de usuario y contraseña)

**Acciones del Usuario:**
1. Accede a la página de login
2. Ingresa su código de usuario
3. Ingresa su contraseña
4. Hace clic en "Iniciar Sesión"

**Endpoints Involucrados:**
- `POST /api/v1/auth/login`

**Request:**
```json
{
  "usuario": "JPEREZ",
  "password": "contraseña123"
}
```

**Response Exitosa (200):**
```json
{
  "error": 0,
  "respuesta": "Autenticación exitosa",
  "resultado": {
    "token": "1|abcdef123456...",
    "user": {
      "user_id": 1,
      "user_code": "JPEREZ",
      "tipo_usuario": "usuario",
      "usuario_id": 5,
      "cliente_id": null,
      "es_supervisor": false,
      "nombre": "Juan Pérez",
      "email": "usuario@ejemplo.com"
    }
  }
}
```

**Nota:** Los valores `tipo_usuario`, `user_code`, `usuario_id`/`cliente_id`, y `es_supervisor` se conservan durante todo el ciclo del proceso y están disponibles en cada request autenticado.

**Response Error (401):**
```json
{
  "error": 9002,
  "respuesta": "Credenciales inválidas",
  "resultado": {}
}
```

**Validaciones:**
- Código de usuario no puede estar vacío
- Contraseña no puede estar vacía
- User debe existir en tabla `USERS` y estar activo
- Contraseña debe coincidir con el hash almacenado en `USERS`
- User.code debe existir en `PQ_PARTES_CLIENTES` O `PQ_PARTES_USUARIOS`
- Cliente o Usuario asociado debe estar activo y no inhabilitado

**Criterios de Éxito:**
- Usuario recibe token de autenticación
- Token se almacena en el frontend (localStorage/sessionStorage)
- Usuario es redirigido al dashboard principal

**Criterios de Falla:**
- Mensaje de error claro si las credenciales son inválidas
- No se genera token
- Usuario permanece en la página de login

---

### Paso 2: Registro de Tarea Diaria

**Objetivo:** El usuario registra una tarea realizada.

**Precondiciones:**
- Usuario está autenticado (token válido)
- Existen clientes activos en el sistema
- Existen tipos de tarea activos en el sistema

**Acciones del Usuario:**
1. Accede al formulario de registro de tarea
2. Selecciona o ingresa la fecha (por defecto: fecha actual)
3. Selecciona un cliente de la lista
4. Selecciona un tipo de tarea
5. Ingresa la duración (en minutos u horas)
6. Opcionalmente ingresa una observación
7. Hace clic en "Guardar"

**Endpoints Involucrados:**
- `GET /api/v1/clientes` (para cargar lista de clientes)
- `GET /api/v1/tipos-tarea` (para cargar lista de tipos)
- `POST /api/v1/tareas` (para crear el registro)

**Request - Obtener Clientes (GET /api/v1/clientes):**
```json
Headers: {
  "Authorization": "Bearer 1|abcdef123456...",
  "Accept": "application/json"
}
```

**Response:**
```json
{
  "error": 0,
  "respuesta": "Clientes obtenidos correctamente",
  "resultado": [
    {
      "id": 1,
      "nombre": "Cliente A",
      "activo": true
    },
    {
      "id": 2,
      "nombre": "Cliente B",
      "activo": true
    }
  ]
}
```

**Request - Obtener Tipos de Tarea (GET /api/v1/tipos-tarea):**
```json
Headers: {
  "Authorization": "Bearer 1|abcdef123456...",
  "Accept": "application/json"
}
```

**Response:**
```json
{
  "error": 0,
  "respuesta": "Tipos de tarea obtenidos correctamente",
  "resultado": [
    {
      "id": 1,
      "descripcion": "Desarrollo",
      "activo": true
    },
    {
      "id": 2,
      "descripcion": "Reunión",
      "activo": true
    }
  ]
}
```

**Request - Crear Tarea (POST /api/v1/tareas):**
```json
Headers: {
  "Authorization": "Bearer 1|abcdef123456...",
  "Accept": "application/json",
  "Content-Type": "application/json"
}

Body: {
  "fecha": "2025-01-20",
  "cliente_id": 1,
  "tipo_tarea_id": 1,
  "duracion_minutos": 120,
  "observacion": "Desarrollo de funcionalidad X"
}
```

**Response Exitosa (201):**
```json
{
  "error": 0,
  "respuesta": "Tarea registrada correctamente",
  "resultado": {
    "id": 1,
    "usuario_id": 1,
    "cliente_id": 1,
    "tipo_tarea_id": 1,
    "fecha": "2025-01-20",
    "duracion_minutos": 120,
    "observacion": "Desarrollo de funcionalidad X",
    "created_at": "2025-01-20T10:30:00Z",
    "updated_at": "2025-01-20T10:30:00Z"
  }
}
```

**Response Error - Validación (422):**
```json
{
  "error": 1001,
  "respuesta": "La duración debe ser mayor a cero",
  "resultado": {
    "errors": {
      "duracion_minutos": ["La duración debe ser mayor a cero"]
    }
  }
}
```

**Response Error - Fecha Futura (422):**
```json
{
  "error": 1002,
  "respuesta": "La fecha no puede ser futura",
  "resultado": {
    "errors": {
      "fecha": ["La fecha no puede ser futura"]
    }
  }
}
```

**Validaciones:**
- `fecha`: Obligatorio, formato YYYY-MM-DD, no puede ser futura
- `cliente_id`: Obligatorio, debe existir y estar activo
- `tipo_tarea_id`: Obligatorio, debe existir y estar activo
- `duracion_minutos`: Obligatorio, debe ser mayor a 0
- `observacion`: Opcional, texto libre

**Reglas de Negocio:**
- El registro queda automáticamente asociado al usuario autenticado
- La fecha no puede ser futura
- La duración debe ser mayor a cero
- Cliente y tipo de tarea deben estar activos

**Criterios de Éxito:**
- Tarea se guarda correctamente en la base de datos
- Se asocia automáticamente al usuario autenticado
- Usuario recibe confirmación visual
- Formulario se limpia o redirige a la lista de tareas

**Criterios de Falla:**
- Mensajes de error claros por cada validación fallida
- No se crea el registro si alguna validación falla
- Usuario puede corregir y reintentar

---

### Paso 3: Visualización de Resumen

**Objetivo:** El usuario visualiza un resumen de su dedicación.

**Precondiciones:**
- Usuario está autenticado
- Existen tareas registradas por el usuario

**Acciones del Usuario:**
1. Accede a la vista de resumen/dashboard
2. Opcionalmente selecciona un rango de fechas
3. Visualiza el resumen de dedicación por cliente

**Endpoints Involucrados:**
- `GET /api/v1/tareas` (para listar tareas del usuario)
- `GET /api/v1/tareas/resumen` (para obtener resumen agregado)

**Request - Listar Tareas (GET /api/v1/tareas):**
```json
Headers: {
  "Authorization": "Bearer 1|abcdef123456...",
  "Accept": "application/json"
}

Query Parameters (opcionales):
- fecha_desde: "2025-01-01"
- fecha_hasta: "2025-01-31"
- page: 1
- page_size: 20
```

**Response:**
```json
{
  "error": 0,
  "respuesta": "Tareas obtenidas correctamente",
  "resultado": {
    "items": [
      {
        "id": 1,
        "fecha": "2025-01-20",
        "cliente": {
          "id": 1,
          "nombre": "Cliente A"
        },
        "tipo_tarea": {
          "id": 1,
          "descripcion": "Desarrollo"
        },
        "duracion_minutos": 120,
        "duracion_horas": 2.0,
        "observacion": "Desarrollo de funcionalidad X",
        "created_at": "2025-01-20T10:30:00Z"
      }
    ],
    "page": 1,
    "page_size": 20,
    "total": 1,
    "total_pages": 1
  }
}
```

**Request - Resumen de Dedicación (GET /api/v1/tareas/resumen):**
```json
Headers: {
  "Authorization": "Bearer 1|abcdef123456...",
  "Accept": "application/json"
}

Query Parameters (opcionales):
- fecha_desde: "2025-01-01"
- fecha_hasta: "2025-01-31"
```

**Response:**
```json
{
  "error": 0,
  "respuesta": "Resumen obtenido correctamente",
  "resultado": {
    "periodo": {
      "fecha_desde": "2025-01-01",
      "fecha_hasta": "2025-01-31"
    },
    "resumen_por_cliente": [
      {
        "cliente_id": 1,
        "cliente_nombre": "Cliente A",
        "total_minutos": 480,
        "total_horas": 8.0,
        "cantidad_tareas": 4
      },
      {
        "cliente_id": 2,
        "cliente_nombre": "Cliente B",
        "total_minutos": 240,
        "total_horas": 4.0,
        "cantidad_tareas": 2
      }
    ],
    "totales": {
      "total_minutos": 720,
      "total_horas": 12.0,
      "cantidad_tareas": 6
    }
  }
}
```

**Validaciones:**
- Fechas opcionales, si se proporcionan deben ser válidas
- `fecha_desde` no puede ser posterior a `fecha_hasta`
- Solo se muestran tareas del usuario autenticado

**Criterios de Éxito:**
- Se muestran todas las tareas del usuario autenticado
- El resumen agrupa correctamente por cliente
- Los totales son correctos
- La información es clara y legible

**Criterios de Falla:**
- Si no hay tareas, se muestra mensaje apropiado
- Errores de validación de fechas se muestran claramente

---

## Flujo Completo (Diagrama)

```
┌─────────────┐
│   Usuario   │
│  accede a   │
│   sistema   │
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│  POST /login    │
│  (email, pass)  │
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│  Token recibido │
│  (autenticado)  │
└──────┬──────────┘
       │
       ▼
┌──────────────────────────┐
│  GET /clientes           │
│  GET /tipos-tarea        │
│  (cargar catálogos)      │
└──────┬───────────────────┘
       │
       ▼
┌──────────────────────────┐
│  POST /tareas            │
│  (crear registro)        │
└──────┬───────────────────┘
       │
       ▼
┌──────────────────────────┐
│  GET /tareas/resumen     │
│  (visualizar resumen)    │
└──────────────────────────┘
```

---

## Casos de Error y Manejo

### Error de Autenticación
- **Causa:** Token inválido o expirado
- **Acción:** Redirigir a login
- **Código HTTP:** 401

### Error de Validación
- **Causa:** Datos inválidos en el request
- **Acción:** Mostrar mensajes de error específicos
- **Código HTTP:** 422

### Error de Recurso No Encontrado
- **Causa:** Cliente o tipo de tarea no existe
- **Acción:** Mostrar mensaje de error
- **Código HTTP:** 404

### Error del Servidor
- **Causa:** Error inesperado
- **Acción:** Mostrar mensaje genérico, registrar error
- **Código HTTP:** 500

---

## Consideraciones Técnicas

### Seguridad
- Todas las rutas (excepto login) requieren autenticación
- Token se envía en header `Authorization: Bearer {token}`
- Validar token en cada request
- No exponer información sensible en respuestas

### Performance
- Usar eager loading para relaciones (cliente, tipo_tarea)
- Paginar listados grandes
- Índices en campos de búsqueda (usuario_id, fecha)

### Testing
- Test E2E debe cubrir este flujo completo
- Tests unitarios para cada endpoint
- Tests de integración para validaciones y reglas de negocio

---

## Criterios de Aceptación del Flujo E2E

- [ ] Usuario puede autenticarse con credenciales válidas
- [ ] Usuario no puede autenticarse con credenciales inválidas
- [ ] Usuario autenticado puede cargar lista de clientes
- [ ] Usuario autenticado puede cargar lista de tipos de tarea
- [ ] Usuario puede registrar una tarea con datos válidos
- [ ] Sistema valida que la duración sea mayor a cero
- [ ] Sistema valida que la fecha no sea futura
- [ ] Sistema asocia automáticamente la tarea al usuario autenticado
- [ ] Usuario puede ver sus tareas registradas
- [ ] Usuario puede ver resumen de dedicación por cliente
- [ ] Solo se muestran tareas del usuario autenticado
- [ ] Los totales del resumen son correctos

---

## Notas

- Este flujo es el prioritario del MVP
- Todos los desarrollos deben soportar este flujo
- El test E2E debe validar este flujo completo
- Cualquier cambio que rompa este flujo es considerado breaking change

---

**Última actualización:** 2025-01-20

