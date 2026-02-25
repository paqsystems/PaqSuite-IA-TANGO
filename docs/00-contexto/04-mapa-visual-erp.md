# Mapa Visual del ERP

## Propósito

Este documento permite comprender la estructura global del ERP en menos de 5 minutos.

No describe implementación.
No describe código.
Describe cómo se organiza el sistema.

---

# 1️⃣ Vista Conceptual General

```mermaid
flowchart TD

A[Usuario] --> B[Autenticación]
B --> C{Empresa Seleccionada}
C -->|X-Company-Id| D[Contexto Empresa]

D --> E[Base Diccionario]
D --> F[Base Empresa]

E --> E1[Usuarios]
E --> E2[Roles]
E --> E3[Permisos]
E --> E4[Empresas]

F --> F1[Clientes]
F --> F2[Ventas]
F --> F3[Compras]
F --> F4[Stock]
F --> F5[Tesorería]
```

# 2️⃣ Estructura Documental

```mermaid
flowchart TD

A[00-contexto] --> B[01-arquitectura]
B --> C[02-producto]
C --> D[03-hu-historias]
D --> E[04-tareas]
E --> F[Backend / Frontend / DB]
F --> G[05-testing]
G --> H[06-operacion]
H --> I[07-seguridad]
```

# 3️⃣ Flujo de Desarrollo

```mermaid
flowchart LR

A[Contexto] --> B[Arquitectura]
B --> C[Flujo E2E]
C --> D[HU]
D --> E[TR]
E --> F[Código]
F --> G[Tests]
G --> H[Validación]
```

Nunca se comienza en "Código".

# 4️⃣ Modelo de Gobernanza

```mermaid
flowchart TD

A[Desarrollador] --> B[HU]
B --> C{Impacto Arquitectónico?}

C -->|No| D[Implementación]
C -->|Sí| E[Revisión Arquitecto]

E --> F[Documentar Decisión]
F --> D
```
