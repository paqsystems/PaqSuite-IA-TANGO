# Arquitectura del Sistema – MVP

## Visión general
Arquitectura web clásica de tres capas, optimizada para un MVP.

Frontend (Web)
|
v
Backend API (REST)
|
v
Base de Datos


---

## Componentes

### Frontend
- Aplicación web SPA
- Funciones:
  - Login
  - Registro de tareas
  - Listado y resumen
- Comunicación vía API REST

---

### Backend
- API REST
- Responsabilidades:
  - Autenticación
  - Validaciones de negocio
  - Persistencia de datos
- Control de acceso por usuario autenticado

---

### Base de datos
- Relacional
- Entidades normalizadas
- Índices en claves foráneas y fechas

---

## Decisiones clave
- No se usan microservicios.
- No se usan colas ni eventos.
- Se prioriza claridad sobre escalabilidad futura.
