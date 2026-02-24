# Documentación Institucional – Contexto del ERP

## Propósito de esta Carpeta

La carpeta `00-contexto/` contiene los documentos institucionales y estratégicos del ERP.

Estos documentos:

- No describen código.
- No describen implementación técnica.
- No son historias de usuario.
- No son tareas.

Definen el marco conceptual, metodológico y de gobierno del sistema.

Todo desarrollador debe leer al menos los documentos operativos antes de comenzar a trabajar.

---

# Orden de Lectura Recomendado

## Nivel 1 – Comprensión General (Obligatorio para todos)

1. `00-contexto-global-erp.md`  
   Define qué es el ERP, su modelo multiempresa, seguridad y principios estructurales.

2. `01-guia-estructura-documental-corporativa.md`  
   Explica cómo está organizada la documentación del proyecto.

3. `02-guia-onboarding-30-minutos.md`  
   Guía rápida para incorporarse al proyecto correctamente.

4. `03-checklist-proyecto-erp.md`  
   Define el proceso obligatorio de trabajo (HU → TR → Código → Tests).

---

## Nivel 2 – Gobierno Arquitectónico (Para roles senior y arquitectos)

10. `10-manual-arquitecto-erp.md`  
    Define responsabilidades y principios del Arquitecto del ERP.

11. `11-gobierno-decisiones-tecnicas.md`  
    Define cómo se toman decisiones técnicas estructurales.

12. `12-politica-evolucion-modulos.md`  
    Define cómo deben crecer y evolucionar los módulos del ERP.

---

# Qué NO es esta Carpeta

- No contiene especificaciones técnicas.
- No contiene historias de usuario.
- No contiene tareas técnicas.
- No contiene contratos de API.

Es documentación de marco institucional.

---

# Jerarquía Documental del Proyecto

El ERP se organiza en niveles:

1. Contexto Institucional → `00-contexto/`
2. Arquitectura → `01-arquitectura/`
3. Producto y Flujos → `02-producto/`
4. Historias de Usuario → `03-hu-historias/`
5. Tareas Técnicas → `04-tareas/`
6. Testing → `05-testing/`
7. Operación → `06-operacion/`
8. Seguridad → `07-seguridad/`

Cada carpeta cumple una función distinta y no debe mezclarse con otra.

---

# Regla Fundamental

Antes de programar:

- Leer contexto.
- Leer arquitectura.
- Leer la HU.
- Confirmar impacto.

El orden es parte de la disciplina del proyecto.

---

# Responsabilidad Profesional

El desconocimiento del contexto no justifica decisiones incorrectas.

Todo desarrollador es responsable de comprender el marco institucional antes de modificar el sistema.
