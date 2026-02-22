# Especificaciones de Frontend

## Descripción General

Este documento define las especificaciones técnicas y de diseño para el frontend del MVP Sistema de Registro de Tareas.

---

## Requisitos de Build y Deploy (OBLIGATORIO)

**Antes de cualquier push, merge a `main` o deploy:**

1. Ejecutar `cd frontend && npm run build` y verificar que termina sin errores.
2. Si el build falla, consultar `.cursor/rules/22-frontend-build-typescript.md` (incluye tabla de errores frecuentes).

**Errores recurrentes en Vercel:** Ver `lidr - frontend.txt` en el repo para el listado exacto. La regla 22 mapea cada error a su solución.

---

## Stack Tecnológico

### Framework

**Recomendación:** React, Vue o Angular  
**Decisión:** React

### Build Tool

- **Vite** (recomendado) - Rápido y moderno

### State Management

- **React:** Context API 

### UI Library / Styling

**Opciones:**
- Material-UI (React)
- Ant Design (React/Vue)
- Tailwind CSS (Framework agnóstico)
- Bootstrap (Framework agnóstico)

**Decisión:** 
- **UI Library:** Tailwind CSS + shadcn/ui (Radix UI)
- **Styling:** Tailwind CSS para estilos utilitarios
- **Componentes:** shadcn/ui (basado en Radix UI) para componentes accesibles y personalizables
- **Arquitectura:** Los componentes de shadcn/ui se instalan como código fuente en `src/shared/ui/`, permitiendo personalización completa

---

## Arquitectura

### Separación de Responsabilidades (CSS, HTML, JS)

**Principio fundamental:** Mantener separación clara entre CSS, HTML/JSX y JavaScript en toda la aplicación.

#### Reglas de Separación

1. **CSS/Estilos:**
   - Archivos separados (`.css`, `.scss`, `.module.css`)
   - NO usar estilos inline (excepto casos muy específicos y justificados)
   - Estilos globales en `src/styles/`
   - Estilos por componente en archivos dedicados

2. **HTML/JSX:**
   - Estructura en archivos de componente (`.tsx`, `.jsx`, `.vue`)
   - Separado de la lógica de negocio
   - Props para pasar datos, no lógica compleja

3. **JavaScript/TypeScript:**
   - Lógica de negocio en archivos separados
   - Hooks personalizados para lógica reutilizable
   - Servicios para llamadas API
   - Utilidades en archivos dedicados

#### Estructura por Componente

```
components/
├── LoginForm/
│   ├── LoginForm.tsx           # Estructura JSX y lógica de presentación
│   ├── LoginForm.module.css    # Estilos específicos del componente
│   ├── LoginForm.test.tsx      # Tests del componente
│   └── useLoginForm.ts         # Lógica de negocio (custom hook)
```

#### Convenciones por Framework

**React:**
- CSS Modules (`.module.css`) - Recomendado
- Styled Components - Aceptable si se mantiene en archivos separados
- Archivos `.css` importados - Aceptable
- ❌ Estilos inline - Evitar

**Vue:**
- `<style scoped>` en `.vue` - Aceptable para estilos simples
- Archivos `.css` separados - Preferido para estilos complejos
- ❌ Estilos inline - Evitar

**Angular:**
- Archivos `.component.css` - Obligatorio (separación por defecto)
- Estilos globales en `styles.css`
- ❌ Estilos inline - Evitar

#### Ejemplo de Implementación Correcta

```typescript
// LoginForm.tsx - Solo estructura y lógica de presentación
import { useLoginForm } from './useLoginForm';
import styles from './LoginForm.module.css';

export function LoginForm() {
  const { formData, handleChange, handleSubmit, error } = useLoginForm();
  
  return (
    <form className={styles.form} onSubmit={handleSubmit}>
      <input 
        className={styles.input}
        value={formData.usuario}
        onChange={handleChange}
      />
      <button className={styles.button} type="submit">
        Iniciar Sesión
      </button>
      {error && <div className={styles.error}>{error}</div>}
    </form>
  );
}
```

```css
/* LoginForm.module.css - Solo estilos */
.form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.input {
  padding: 0.5rem;
  border: 1px solid #ccc;
}

.button {
  padding: 0.75rem;
  background-color: #007bff;
  color: white;
}

.error {
  color: red;
}
```

```typescript
// useLoginForm.ts - Solo lógica de negocio
import { useState } from 'react';
import { authService } from '@/services/auth.service';

export function useLoginForm() {
  const [formData, setFormData] = useState({ usuario: '', password: '' });
  const [error, setError] = useState<string | null>(null);
  
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };
  
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const response = await authService.login(formData);
      // Manejo de respuesta...
    } catch (err) {
      setError('Error al iniciar sesión');
    }
  };
  
  return { formData, handleChange, handleSubmit, error };
}
```

#### Beneficios de la Separación

- **Mantenibilidad:** Fácil localizar y modificar estilos o lógica
- **Reutilización:** Estilos y lógica pueden reutilizarse independientemente
- **Testing:** Más fácil testear lógica sin preocuparse por estilos
- **Colaboración:** Diferentes desarrolladores pueden trabajar en estilos y lógica simultáneamente
- **Performance:** Mejor optimización y tree-shaking

---

### Estructura de Carpetas

```
frontend/
├── src/
│   ├── shared/
│   │   ├── ui/             # UI Layer Wrappers (OBLIGATORIO)
│   │   │   ├── Button/
│   │   │   │   ├── Button.tsx
│   │   │   │   ├── Button.module.css
│   │   │   │   └── index.ts
│   │   │   ├── TextField/
│   │   │   ├── DataTable/
│   │   │   ├── Modal/
│   │   │   └── index.ts    # Exportación centralizada
│   │   └── i18n/           # Función t() de traducción
│   │       ├── t.ts
│   │       └── index.ts
│   ├── features/           # Features del dominio (auth, tasks, etc.)
│   ├── pages/              # Páginas/vistas principales
│   │   ├── Login/
│   │   │   ├── LoginPage.tsx         # Estructura JSX
│   │   │   ├── LoginPage.module.css  # Estilos de la página
│   │   │   ├── useLoginPage.ts       # Lógica de negocio (hook)
│   │   │   └── LoginPage.test.tsx    # Tests
│   │   ├── Dashboard/
│   │   ├── TaskEntry/
│   │   ├── TaskList/
│   │   └── TaskSummary/
│   ├── services/           # Servicios API (solo lógica)
│   │   ├── api.ts          # Cliente HTTP base
│   │   ├── auth.service.ts
│   │   ├── tasks.service.ts
│   │   └── clients.service.ts
│   ├── hooks/              # Custom hooks (lógica reutilizable)
│   ├── store/              # State management
│   ├── utils/              # Utilidades (lógica pura)
│   │   ├── formatters.ts
│   │   ├── validators.ts
│   │   └── constants.ts
│   ├── styles/             # Estilos globales
│   │   ├── variables.css   # Variables CSS (colores, tamaños, etc.)
│   │   ├── reset.css       # Reset CSS (opcional)
│   │   └── global.css      # Estilos globales
│   ├── locales/           # Traducciones i18n
│   └── App.tsx             # Componente raíz
├── tests/                  # Tests
│   ├── unit/
│   ├── integration/
│   └── e2e/
├── public/                 # Archivos estáticos
└── package.json
```

**Nota importante sobre separación de responsabilidades:**
Cada componente debe mantener separación clara:
- **Estructura (JSX/HTML):** En archivos `.tsx`/`.jsx`/`.vue`
- **Estilos (CSS):** En archivos `.css`/`.module.css`/`.scss` separados
- **Lógica (JS/TS):** En hooks, servicios o archivos de utilidades separados
- **NO** usar estilos inline (excepto casos muy específicos y justificados)

**Regla de UI Layer Wrappers:**
- **Nadie importa librerías UI externas en features**
- **Todo componente visual reutilizable vive en `src/shared/ui/`**
- **Las features solo importan desde `src/shared/ui`**
- Ver documentación completa: `docs/frontend/ui-layer-wrappers.md`

---

## Layout general y navegación

### Header de la aplicación

**Definición general:** El header de la aplicación (barra superior con título del sistema, nombre del usuario, rol y botón de cerrar sesión) **debe permanecer visible en todas las pantallas** una vez que el usuario está autenticado. No se debe ocultar ni reemplazar el header al navegar entre dashboard, lista de tareas, perfil, etc.

- **Alcance:** Todas las rutas protegidas (dentro del layout autenticado).
- **Contenido típico:** Título de la aplicación, nombre del usuario logueado, indicador de rol (ej. "Supervisor"), botón "Cerrar sesión".
- **Objetivo:** Consistencia visual y acceso permanente a la identidad de sesión y cierre.

---

## Páginas Principales

### 1. Login

**Ruta:** `/login`  
**Componente:** `LoginPage`

**Elementos:**
- Formulario de login
- Campo: código de usuario
- Campo: contraseña
- Botón: "Iniciar Sesión"
- Manejo de errores

**Flujo:**
1. Usuario ingresa credenciales
2. Submit → POST /api/v1/auth/login
3. Si éxito: guardar token, redirigir a dashboard
4. Si error: mostrar mensaje

**Test IDs (OBLIGATORIOS):**
- `login-form` - Contenedor del formulario
- `login-form-usuario-input` - Input de código de usuario
- `login-form-password-input` - Input de contraseña
- `login-form-submit-button` - Botón de envío
- `login-form-error-message` - Mensaje de error

**Accesibilidad (OBLIGATORIA):**
- Todos los inputs deben tener `aria-label` o `<label>` asociado
- Campo usuario: `aria-required="true"`
- Campo password: `aria-required="true"`
- Mensaje de error: `role="alert"`, `aria-live="polite"`
- Botón submit: `aria-label="Iniciar sesión"`
- Navegación por teclado funcional

---

### 2. Dashboard / Registro de Tarea

**Ruta:** `/` o `/dashboard`  
**Componente:** `DashboardPage` o `TaskEntryPage`

**Elementos:**
- Formulario de registro de tarea
- Campo: fecha (date picker)
- Campo: cliente (select/dropdown)
- Campo: tipo de tarea (select/dropdown)
- Campo: duración en minutos (number input)
- Checkbox: sin cargo
- Checkbox: presencial
- Campo: observación (textarea, opcional)
- Botón: "Registrar Tarea"

**Validaciones Frontend:**
- Fecha: formato YYYY-MM-DD, no futura
- Cliente: requerido
- Tipo de tarea: requerido
- Duración: > 0, <= 1440
- Observación: max 1000 caracteres

**Test IDs (OBLIGATORIOS):**
- `task-entry-form` - Contenedor del formulario
- `task-entry-date-input` - Input de fecha
- `task-entry-cliente-select` - Select de cliente
- `task-entry-tipo-select` - Select de tipo de tarea
- `task-entry-duracion-input` - Input de duración
- `task-entry-sin-cargo-checkbox` - Checkbox sin cargo
- `task-entry-presencial-checkbox` - Checkbox presencial
- `task-entry-observacion-textarea` - Textarea de observación
- `task-entry-submit-button` - Botón de envío
- `task-entry-error-message` - Mensaje de error general
- `task-entry-success-message` - Mensaje de éxito

**Accesibilidad (OBLIGATORIA):**
- Todos los campos deben tener `<label>` asociado con `htmlFor`
- Campos requeridos: `aria-required="true"`
- Campos con error: `aria-invalid="true"`, `aria-describedby` apuntando al mensaje de error
- Mensajes de error: `role="alert"`, `aria-live="polite"`
- Botón submit: `aria-label="Registrar tarea"`
- Checkboxes: `aria-label` descriptivo
- Navegación por teclado funcional en todo el formulario

---

### 3. Lista de Tareas

**Ruta:** `/tareas`  
**Componente:** `TaskListPage`

**Elementos:**
- Lista de tareas del usuario
- Filtros: fecha desde, fecha hasta
- Paginación
- Ordenamiento: por fecha, duración
- Acciones por tarea: editar, eliminar

**Test IDs (OBLIGATORIOS):**
- `task-list` - Contenedor de la lista
- `task-list-item-{id}` - Item individual de tarea
- `task-list-filters` - Contenedor de filtros
- `task-list-filter-date-from` - Filtro fecha desde
- `task-list-filter-date-to` - Filtro fecha hasta
- `task-list-apply-filters-button` - Botón aplicar filtros
- `task-list-pagination` - Contenedor de paginación
- `task-list-pagination-next` - Botón siguiente página
- `task-list-pagination-previous` - Botón página anterior
- `task-list-item-{id}-edit-button` - Botón editar tarea
- `task-list-item-{id}-delete-button` - Botón eliminar tarea
- `task-list-empty-message` - Mensaje cuando no hay tareas

**Accesibilidad (OBLIGATORIA):**
- Lista debe tener `role="list"` o usar `<ul>`
- Items deben tener `role="listitem"` o usar `<li>`
- Botones de acción: `aria-label` descriptivo
- Filtros: `<label>` asociado y `aria-required` si aplica
- Paginación: `aria-label` para botones de navegación
- Mensaje vacío: `role="status"`, `aria-live="polite"`

---

### 4. Resumen de Dedicación

**Ruta:** `/resumen`  
**Componente:** `TaskSummaryPage`

**Elementos:**
- Resumen total de horas
- Desglose por cliente
- Filtros: rango de fechas
- Gráficos/tablas (opcional)

**Test IDs:**
- `task-summary`
- `task-summary-total-hours`
- `task-summary-by-client`
- `task-summary-filters`

---

## Servicios API

### Cliente HTTP Base

```typescript
// Ejemplo con axios
import axios from 'axios';

const apiClient = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Interceptor para agregar token
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Interceptor para manejar errores
apiClient.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      // Redirigir a login
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
```

### Servicio de Autenticación

```typescript
interface LoginRequest {
  usuario: string;
  password: string;
}

interface LoginResponse {
  error: number;
  respuesta: string;
  resultado: {
    token: string;
    user: {
      id: number;
      code: string;
      nombre: string;
      email: string;
    };
  } | null;
}

export const authService = {
  login: async (credentials: LoginRequest): Promise<LoginResponse> => {
    return apiClient.post('/auth/login', credentials);
  }
};
```

### Servicio de Tareas

```typescript
export const tasksService = {
  create: async (task: TaskCreateRequest): Promise<ApiResponse<Task>> => {
    return apiClient.post('/tareas', task);
  },
  
  list: async (filters?: TaskListFilters): Promise<ApiResponse<TaskListResult>> => {
    return apiClient.get('/tareas', { params: filters });
  },
  
  update: async (id: number, task: TaskUpdateRequest): Promise<ApiResponse<Task>> => {
    return apiClient.put(`/tareas/${id}`, task);
  },
  
  delete: async (id: number): Promise<ApiResponse<void>> => {
    return apiClient.delete(`/tareas/${id}`);
  },
  
  summary: async (filters?: SummaryFilters): Promise<ApiResponse<SummaryResult>> => {
    return apiClient.get('/tareas/resumen', { params: filters });
  }
};
```

---

## Manejo de Estado

### Estado de Autenticación

```typescript
// Ejemplo con Context (React)
interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthState | null>(null);
```

### Estado de Tareas

```typescript
interface TasksState {
  tasks: Task[];
  loading: boolean;
  error: string | null;
  filters: TaskListFilters;
  pagination: PaginationInfo;
}
```

---

## Validaciones

### Validadores Reutilizables

```typescript
export const validators = {
  required: (value: any) => {
    if (!value || (typeof value === 'string' && value.trim() === '')) {
      return 'Este campo es obligatorio';
    }
    return null;
  },
  
  dateFormat: (value: string) => {
    const regex = /^\d{4}-\d{2}-\d{2}$/;
    if (!regex.test(value)) {
      return 'Formato de fecha inválido (YYYY-MM-DD)';
    }
    return null;
  },
  
  dateNotFuture: (value: string) => {
    const date = new Date(value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (date > today) {
      return 'La fecha no puede ser futura';
    }
    return null;
  },
  
  min: (min: number) => (value: number) => {
    if (value < min) {
      return `El valor debe ser mayor o igual a ${min}`;
    }
    return null;
  },
  
  max: (max: number) => (value: number) => {
    if (value > max) {
      return `El valor debe ser menor o igual a ${max}`;
    }
    return null;
  },
  
  maxLength: (max: number) => (value: string) => {
    if (value.length > max) {
      return `Máximo ${max} caracteres`;
    }
    return null;
  }
};
```

---

## Formateo de Datos

```typescript
export const formatters = {
  date: (date: string | Date): string => {
    const d = typeof date === 'string' ? new Date(date) : date;
    return d.toLocaleDateString('es-ES');
  },
  
  duration: (minutes: number): string => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hours > 0) {
      return `${hours}h ${mins}m`;
    }
    return `${mins}m`;
  },
  
  durationHours: (minutes: number): number => {
    return round(minutes / 60, 2);
  }
};
```

---

## Manejo de Errores

### Tipos de Error

```typescript
interface ApiError {
  error: number;
  respuesta: string;
  resultado: {
    errors?: Record<string, string[]>;
  } | null;
}
```

### Componente de Error (con Accesibilidad y Test IDs)

```typescript
function ErrorMessage({ error, testId = "error-message" }: { error: ApiError; testId?: string }) {
  if (error.resultado?.errors) {
    // Errores de validación
    return (
      <div 
        data-testid="form-validation-errors"
        role="alert"
        aria-live="polite"
        aria-atomic="true"
      >
        {Object.entries(error.resultado.errors).map(([field, messages]) => (
          <div key={field} data-testid={`${field}-errors`}>
            {messages.map((msg, i) => (
              <p key={i} role="alert">{msg}</p>
            ))}
          </div>
        ))}
      </div>
    );
  }
  
  // Error general
  return (
    <div 
      data-testid={testId}
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
    >
      {error.respuesta}
    </div>
  );
}
```

### Asociación de Errores con Campos (Accesibilidad)

```typescript
// Ejemplo: Input con error asociado usando data-testid + ARIA
<div>
  <label htmlFor="task-date">
    Fecha
  </label>
  <input 
    id="task-date"
    data-testid="task-entry-date-input"
    type="date"
    aria-required="true"
    aria-invalid={hasError}
    aria-describedby={hasError ? "task-date-error" : undefined}
  />
  {hasError && (
    <div 
      id="task-date-error"
      data-testid="task-entry-date-error"
      role="alert"
      aria-live="polite"
    >
      {errorMessage}
    </div>
  )}
</div>
```

---

## Rutas y Navegación

### Configuración de Rutas

```typescript
// Ejemplo React Router
const routes = [
  {
    path: '/login',
    element: <LoginPage />,
    public: true
  },
  {
    path: '/',
    element: <DashboardPage />,
    protected: true
  },
  {
    path: '/tareas',
    element: <TaskListPage />,
    protected: true
  },
  {
    path: '/resumen',
    element: <TaskSummaryPage />,
    protected: true
  }
];
```

### Protección de Rutas

```typescript
function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const token = localStorage.getItem('token');
  
  if (!token) {
    return <Navigate to="/login" />;
  }
  
  return <>{children}</>;
}
```

---

## Responsive Design

### Breakpoints

- **Mobile:** < 768px
- **Tablet:** 768px - 1024px
- **Desktop:** > 1024px

### Consideraciones

- Formularios adaptables a móvil
- Tablas con scroll horizontal en móvil
- Navegación hamburguesa en móvil

---

## Performance

### Optimizaciones

- Code splitting por ruta
- Lazy loading de componentes pesados
- Memoización de componentes costosos
- Debounce en búsquedas/filtros

---

## Reglas Obligatorias de Testing y Accesibilidad

### Test IDs (data-testid) - OBLIGATORIO

**REGLA FUNDAMENTAL:** TODOS los controles interactivos DEBEN tener `data-testid`.

**Aplicable a:**
- ✅ Botones (submit, cancel, delete, edit, etc.)
- ✅ Inputs (text, date, number, email, password, etc.)
- ✅ Selects/Dropdowns
- ✅ Checkboxes y Radio buttons
- ✅ Textareas
- ✅ Enlaces de navegación
- ✅ Formularios (contenedores)
- ✅ Mensajes (error, success, warning)
- ✅ Contenedores principales (listas, tablas)
- ✅ Modales/Dialogs
- ✅ Tabs/Pestañas

**NO aplicar a:**
- ❌ Elementos decorativos puros
- ❌ Textos estáticos sin interacción

**Convención:** `{componente}-{accion}-{elemento}`

**Uso para TDD con Playwright:**
Los `data-testid` son esenciales para Test-Driven Development con Playwright. Permiten escribir tests antes de implementar componentes.

### Accesibilidad (A11y) - OBLIGATORIA

**REGLA FUNDAMENTAL:** Todos los componentes deben ser accesibles.

**Checklist obligatorio:**
- [ ] Todos los controles tienen `data-testid`
- [ ] Todos los inputs tienen `aria-label` o `<label>` asociado
- [ ] Campos requeridos tienen `aria-required="true"`
- [ ] Campos con error tienen `aria-invalid="true"` y `aria-describedby`
- [ ] Mensajes de error tienen `role="alert"` y `aria-live`
- [ ] Botones tienen `aria-label` descriptivo
- [ ] Navegación por teclado funciona (Tab, Enter, Esc)
- [ ] Focus visible y lógico
- [ ] Contraste mínimo WCAG AA (4.5:1)
- [ ] Semántica HTML correcta

**Optimizaciones de Testing:**
- Selectores estables (no se rompen con cambios CSS)
- Tests más rápidos (selectores por atributo son eficientes)
- TDD facilitado (tests antes de implementar)
- Debugging mejorado (fácil identificar elementos)

## Referencias

- `specs/endpoints/` - Especificaciones de endpoints API
- `specs/contracts/response-envelope.md` - Formato de respuesta
- `docs/frontend/i18n.md` - Internacionalización
- `docs/frontend/testing.md` - Testing frontend
- `docs/frontend/ui-layer-wrappers.md` - Arquitectura de UI Layer Wrappers (OBLIGATORIO)
- `.cursor/rules/10-i18n-and-testid.md` - Reglas de test-ids y accesibilidad
- `.cursor/rules/22-frontend-build-typescript.md` - Reglas de build, TypeScript y compatibilidad con deploy (Vercel, AWS)

---

**Última actualización:** 2026-02-11

