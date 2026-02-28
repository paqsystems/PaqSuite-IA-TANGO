# Guía de Estructura Documental Corporativa

**Nivel : Operativo (Uso General)**

## Manual para Programadores – ERP

---

## 1. Propósito de este Documento

Este documento explica:

- Cómo está organizada la documentación corporativa.
- Qué tipo de información se define en cada carpeta.
- En qué momento del proyecto se debe crear cada documento.
- Cómo se mantiene la trazabilidad.

No define código.
No define arquitectura técnica.
Define disciplina documental.

---

# 2. Principio General

En esta organización:

La documentación no es opcional.
La documentación no se escribe al final.
La documentación acompaña cada decisión.

Toda implementación debe poder responder:

- ¿Qué requerimiento la originó?
- ¿Dónde está definida?
- ¿Qué tarea la desarrolló?
- ¿Qué test la valida?

---

# 3. Estructura General de Documentación

La carpeta `docs/` se divide en áreas claramente diferenciadas:
docs/
00-contexto/
01-arquitectura/
02-producto/
03-historias-usuario/
04-tareas/
05-testing/
06-operacion/
07-seguridad/


Cada carpeta tiene una función específica.

---

# 4. ¿Qué se define en cada carpeta?

---

## 4.1 00-contexto/

Contiene documentos institucionales y conceptuales.

Se define aquí:

- Contexto global del ERP.
- Principios generales.
- Guías internas.
- Documentos de formación.

NO se documentan funcionalidades específicas aquí.

Ejemplo (Módulo Autorización):
Aquí NO se describe cómo funciona el login.
Solo podrían describirse principios generales de identidad del sistema.

---

## 4.2 01-arquitectura/

Define decisiones estructurales y técnicas de alto nivel.

Se documenta aquí cuando:

- Se toma una decisión que afecta diseño.
- Se define el modelo de autenticación.
- Se define separación entre base diccionario y bases empresa.
- Se definen mecanismos de autorización.

Ejemplo (Autorización al sistema):
Aquí se documentaría:

- El modelo de autenticación (token, sesiones, etc.).
- La separación identidad vs permisos.
- La relación usuario → empresa → rol.
- El flujo conceptual de validación.

No se documentan pantallas ni historias aquí.

---

## 4.3 02-producto/

Define el comportamiento funcional del sistema.

Se documenta aquí cuando:

- Se define qué hace el sistema.
- Se describe el flujo funcional.
- Se define el alcance del módulo.

Ejemplo (Autorización al sistema):

Aquí se documentaría:

- Qué es el login.
- Qué puede hacer un usuario luego de autenticarse.
- Qué ocurre si falla la autenticación.
- Qué ocurre si no tiene permisos.

Es lenguaje funcional.
No técnico.

---

## 4.4 03-historias-usuario/

Contiene las Historias de Usuario (HU).

Se crea una HU cuando:

- Existe una necesidad funcional concreta.
- Se puede describir desde el punto de vista de un usuario.
- Tiene criterios de aceptación verificables.

Ejemplo:

HU-000X – Inicio de sesión de usuario

Describe:

- Como usuario quiero iniciar sesión...
- Criterios de aceptación.
- Permisos requeridos.
- Validaciones esperadas.

Aquí no se describe cómo se programa.
Solo qué debe suceder.

---

## 4.5 04-tareas/

Contiene Tareas Técnicas (TR).

Se crea una tarea cuando:

- Una HU necesita ser implementada.
- Se requieren cambios en backend, frontend o base de datos.
- Se necesita modificar arquitectura.
- Se deben agregar tests.

Ejemplo (Autorización):

TR-000X – Implementar endpoint de login
TR-000Y – Implementar validación de credenciales
TR-000Z – Implementar generación de token

Las tareas traducen la HU a acciones técnicas.

---

## 4.6 05-testing/

Define cómo se valida el sistema.

Se documenta aquí:

- Estrategia de testing.
- Matriz de trazabilidad.
- Criterios mínimos de validación.

Ejemplo (Autorización):

Se debe registrar:

- Test unitario para validación de credenciales.
- Test de integración para endpoint login.
- Test E2E para flujo completo de autenticación.

---

## 4.7 06-operacion/

Define cómo se opera el sistema en producción.

Se documenta aquí cuando:

- Se requiere procedimiento de despliegue.
- Se requiere protocolo de incidentes.
- Se requiere guía de soporte.

Ejemplo (Autorización):

Cómo reiniciar servicio de autenticación.
Qué hacer si falla emisión de tokens.
Cómo bloquear usuario.

---

## 4.8 07-seguridad/

Define análisis de amenazas y controles.

Se documenta aquí cuando:

- Se identifican riesgos.
- Se define política de contraseñas.
- Se definen reglas de bloqueo.
- Se establecen medidas de protección.

Ejemplo (Autorización):

- Intentos fallidos máximos.
- Política de expiración.
- Encriptación de credenciales.
- Protección contra ataques.

---

# 5. Flujo Correcto de Trabajo

El orden correcto es:

1. Definir contexto (si corresponde).
2. Definir alcance funcional en producto.
3. Crear HU.
4. Descomponer en tareas técnicas.
5. Implementar código.
6. Agregar tests.
7. Actualizar documentación impactada.

Nunca al revés.

---

# 6. Errores Comunes que Deben Evitarse

- Programar sin HU.
- Crear tareas sin HU asociada.
- Mezclar arquitectura con producto.
- Documentar al final.
- No actualizar matriz de trazabilidad.
- Tomar decisiones técnicas sin registrarlas en arquitectura.

---

# 7. Responsabilidad Profesional

Todo programador debe:

- Leer el contexto global antes de trabajar.
- Leer la HU completa antes de comenzar.
- Entender el impacto arquitectónico.
- Actualizar documentación si modifica comportamiento.

La documentación no es burocracia.
Es parte del diseño profesional.

---

# 8. Conclusión

La estructura documental no es decorativa.

Es la base que permite:

- Escalabilidad.
- Orden.
- Onboarding rápido.
- Auditoría clara.
- Evolución futura.

Cada carpeta cumple una función distinta.

Comprender esa función es parte esencial del rol del programador dentro del ERP.
