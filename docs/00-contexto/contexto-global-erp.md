# Contexto Global – Plataforma ERP Corporativa

## 1. Propósito del Proyecto

La plataforma ERP tiene como objetivo constituir un sistema de gestión empresarial integral, adaptable a múltiples organizaciones, con capacidad de operar bajo un modelo multiempresa dentro de una misma instalación.

El sistema debe permitir administrar procesos comerciales, administrativos y financieros de manera estructurada, segura y escalable.

Este documento describe el marco conceptual general del proyecto, sin entrar en decisiones técnicas de implementación.


---

## 2. Modelo Conceptual General

La plataforma se basa en un modelo de:

- Multiusuario
- Multiempresa
- Multirrol
- Base central de configuración + bases operativas por empresa

El sistema distingue claramente entre:

1. Información estructural del sistema (configuración global)
2. Información operativa de cada empresa


---

## 3. Base de Datos “Diccionario” (Contexto Central)

Existe una base de datos central que cumple función estructural y organizativa.

Esta base contiene:

- Usuarios del sistema
- Empresas registradas en la instalación
- Roles disponibles
- Permisos asociados a cada rol
- Asignaciones usuario → empresa → rol
- Definición de menú y opciones habilitadas
- Configuraciones globales
- Repositorios genéricos (tareas programadas, definiciones de reportes, formatos, etc.)

Esta base no contiene información operativa del negocio (clientes, ventas, stock, etc.).

Su función es:

- Administrar identidad
- Administrar seguridad
- Administrar acceso
- Administrar estructura del sistema


---

## 4. Bases de Datos por Empresa (Contexto Operativo)

Cada empresa definida en el sistema posee su propia base de datos operativa.

En estas bases se almacena exclusivamente:

- Información comercial
- Información contable
- Información financiera
- Inventarios
- Movimientos
- Clientes
- Proveedores
- Documentos
- Transacciones

La separación por empresa garantiza:

- Aislamiento de datos
- Seguridad
- Independencia operativa
- Escalabilidad


---

## 5. Modelo de Seguridad

El sistema opera bajo el siguiente modelo:

- El usuario se autentica una única vez.
- El usuario puede estar asociado a una o más empresas.
- En cada empresa puede poseer distintos roles.
- Cada rol define permisos concretos.
- Los permisos determinan qué acciones puede ejecutar el usuario.

La seguridad se valida en cada operación.


---

## 6. Resolución de Empresa (Tenant)

Para operar sobre datos empresariales, el sistema requiere identificar la empresa activa.

Decisión adoptada (Sombrero Azul – Gobernanza):

- El identificador de empresa se informa en cada request a través de un header específico.
- El sistema valida que el usuario autenticado tenga autorización sobre la empresa indicada.
- No se confía en el identificador de empresa sin validación cruzada con los permisos del usuario.

Esto permite:

- Operaciones seguras
- Claridad en el contexto operativo
- Escalabilidad futura
- Separación conceptual entre identidad y operación


---

## 7. Principios Rectores del ERP

El sistema ERP se rige por los siguientes principios:

1. Separación clara entre configuración y operación.
2. Separación estricta entre empresas.
3. Seguridad basada en roles y permisos.
4. Validación permanente del contexto empresarial.
5. Escalabilidad estructural.
6. Protección del modelo conceptual como activo estratégico.


---

## 8. Alcance Macro

El ERP está concebido para evolucionar incorporando módulos funcionales como:

- Gestión de clientes
- Gestión de ventas
- Compras
- Stock
- Tesorería
- Cuentas corrientes
- Liquidaciones
- Presupuesto económico-financiero
- Integraciones externas

Cada módulo operará respetando el modelo conceptual definido en este documento.


---

## 9. Naturaleza de este Documento

Este documento es conceptual.

No define:
- Estructura de código
- Organización de carpetas
- Frameworks
- Tecnología
- Implementación

Su finalidad es servir como marco de comprensión global antes de cualquier tarea técnica.