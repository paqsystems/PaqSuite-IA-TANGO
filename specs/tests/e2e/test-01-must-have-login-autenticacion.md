# Test E2E Must-Have: Login y Autenticación

## Descripción

Test End-to-End del flujo completo de autenticación de usuario, desde el acceso a la página de login hasta la redirección al dashboard.

## Historia de Usuario

**Como** empleado del sistema  
**Quiero** iniciar sesión con mi código de usuario y contraseña  
**Para** acceder al sistema y registrar mis tareas

## Criterios de Aceptación

- El usuario puede acceder a la página de login
- El usuario puede ingresar su código de usuario y contraseña
- El sistema valida las credenciales correctamente
- El usuario es redirigido al dashboard después de un login exitoso
- El usuario recibe mensaje de error con credenciales inválidas
- El token de autenticación se almacena correctamente

## Flujo Completo

### Paso 1: Acceso a Login
1. Usuario navega a la URL base del sistema
2. Verificar que se redirija automáticamente a `/login` si no está autenticado
3. Verificar que la página de login se cargue correctamente
4. Verificar que los campos de código de usuario y contraseña estén visibles

### Paso 2: Ingreso de Credenciales
1. Usuario ingresa su código de usuario en el campo correspondiente
2. Usuario ingresa su contraseña en el campo correspondiente
3. Verificar que los campos acepten la entrada correctamente

### Paso 3: Envío del Formulario
1. Usuario hace clic en el botón "Iniciar Sesión"
2. Verificar que se muestre estado de carga
3. Verificar que se realice la llamada a la API de login

### Paso 4: Login Exitoso
1. API retorna token y datos de usuario
2. Verificar que el token se almacene (localStorage/sessionStorage)
3. Verificar que se redirija a `/dashboard` o página principal
4. Verificar que el menú de usuario muestre el nombre del usuario
5. Verificar que las rutas protegidas sean accesibles

### Paso 5: Login Fallido
1. API retorna error de credenciales inválidas
2. Verificar que se muestre mensaje de error visible
3. Verificar que el usuario permanezca en la página de login
4. Verificar que los campos no se limpien (para facilitar reintento)

## Casos de Prueba

### Caso 1: Login Exitoso con Usuario Normal
- **Precondición:** Usuario existe en el sistema con credenciales válidas
- **Acción:** Ingresar código y contraseña correctos, hacer clic en "Iniciar Sesión"
- **Resultado Esperado:**
  - Redirección a dashboard
  - Token almacenado
  - Menú muestra nombre de usuario
  - Acceso a funcionalidades del sistema

### Caso 2: Login Exitoso con Supervisor
- **Precondición:** Usuario supervisor existe con credenciales válidas
- **Acción:** Ingresar código y contraseña correctos, hacer clic en "Iniciar Sesión"
- **Resultado Esperado:**
  - Redirección a dashboard
  - Token almacenado
  - Acceso a funcionalidades de supervisor visibles

### Caso 3: Credenciales Inválidas
- **Precondición:** Usuario existe pero contraseña incorrecta
- **Acción:** Ingresar código correcto y contraseña incorrecta, hacer clic en "Iniciar Sesión"
- **Resultado Esperado:**
  - Mensaje de error: "Credenciales inválidas"
  - Usuario permanece en página de login
  - Campos no se limpian

### Caso 4: Usuario No Existe
- **Precondición:** Código de usuario no existe en el sistema
- **Acción:** Ingresar código inexistente y contraseña, hacer clic en "Iniciar Sesión"
- **Resultado Esperado:**
  - Mensaje de error: "Credenciales inválidas"
  - Usuario permanece en página de login

### Caso 5: Campos Vacíos
- **Precondición:** Página de login cargada
- **Acción:** Intentar enviar formulario sin completar campos
- **Resultado Esperado:**
  - Mensajes de validación visibles
  - Formulario no se envía
  - No se realiza llamada a API

### Caso 6: Acceso a Ruta Protegida Sin Autenticación
- **Precondición:** Usuario no autenticado
- **Acción:** Intentar acceder directamente a `/procesos/carga-tareas`
- **Resultado Esperado:**
  - Redirección automática a `/login`
  - Después de login exitoso, redirección a la ruta original solicitada

## Validaciones

- Autenticación funciona correctamente
- Token se almacena y persiste
- Redirecciones funcionan
- Mensajes de error son claros
- Validaciones frontend funcionan
- Rutas protegidas requieren autenticación

## Elementos UI a Verificar

- Campo código de usuario (`auth.login.usuarioInput`)
- Campo contraseña (`auth.login.passwordInput`)
- Botón iniciar sesión (`auth.login.submitButton`)
- Mensaje de error (`auth.login.errorMessage`)
- Estado de carga visible durante autenticación

