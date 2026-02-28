# Política de Evolución de Módulos del ERP

**Nivel : Estratégico (gobierno y arquitectura)**

## 1. Objetivo

Definir cómo deben crecer y evolucionar los módulos del ERP.

Evitar:

- Crecimiento desordenado
- Duplicación de lógica
- Inconsistencias entre módulos
- Acoplamientos innecesarios

---

## 2. Principio Fundamental

Todo módulo debe:

- Respetar el modelo multiempresa.
- Respetar el modelo de permisos.
- Respetar separación diccionario / empresa.
- Integrarse mediante contratos claros.

---

## 3. Incorporación de un Nuevo Módulo

Antes de crear un módulo nuevo:

1. Validar que no exista funcionalidad equivalente.
2. Definir alcance funcional.
3. Definir impacto arquitectónico.
4. Definir modelo de datos.
5. Definir permisos necesarios.
6. Definir flujo E2E mínimo.

No se crean módulos por intuición.
Se crean por necesidad validada.

---

## 4. Evolución de un Módulo Existente

Cuando un módulo crece:

- No debe romper contratos existentes.
- No debe alterar datos históricos.
- No debe mezclar responsabilidades.
- Debe mantener coherencia en permisos.

Cambios significativos requieren revisión arquitectónica.

---

## 5. Separación en Servicios (Futuro)

Un módulo podrá separarse en servicio independiente si:

- Tiene alta carga.
- Tiene ciclo de vida propio.
- Requiere escalabilidad independiente.
- Justifica complejidad adicional.

La separación nunca es automática.
Es decisión estratégica.

---

## 6. Integraciones Externas

Toda integración debe:

- Estar encapsulada.
- No acoplar lógica de negocio interna.
- Poder aislarse si falla.
- No comprometer seguridad.

---

## 7. Control de Deuda Técnica

Cada módulo debe:

- Mantener tests mínimos.
- Evitar duplicación de código.
- Mantener documentación actualizada.
- No acumular lógica en capas incorrectas.

La deuda técnica no documentada es deuda invisible.

---

## 8. Visión a Largo Plazo

Los módulos deben poder:

- Crecer sin colisionar.
- Mantener coherencia funcional.
- Integrarse entre sí.
- Escalar con el negocio.

El ERP no es un conjunto de pantallas.
Es un sistema estructural de largo plazo.
