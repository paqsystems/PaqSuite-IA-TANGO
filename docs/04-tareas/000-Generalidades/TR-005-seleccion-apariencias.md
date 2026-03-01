# TR-005 – Selección de apariencia (look & feel) por empresa

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-005 – Selección de apariencia por empresa |
| Épica              | 000 – Generalidades                        |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Administrador / Usuario con permiso        |
| Dependencias       | HU-011 (Admin empresas), HU-002 (Cambio empresa) |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-005 – Selección de apariencia por empresa](../../03-historias-usuario/000-Generalidades/HU-005-seleccion-apariencias.md)

---

## 1) HU Refinada

- **Título:** Selección de apariencia por empresa
- **Narrativa:** Como administrador o usuario con permiso quiero elegir la apariencia del sistema para cada empresa usando temas DevExtreme.
- **Contexto:** Lista cerrada de temas. Persistencia en PQ_Empresa.Theme. Selector en administración empresas. Carga dinámica CSS al cambiar empresa.
- **In scope:** Columna Theme, selector en admin empresas, ThemeLoader, fallback generic.light.
- **Out of scope:** ThemeBuilder, temas personalizados, apariencia por usuario.

---

## 2) Criterios de Aceptación

- Lista cerrada temas DevExtreme (Generic, Material, Fluent). Cada tema con nombre legible.
- Administrador asigna tema a cada empresa. Configuración en backend; aplica cuando usuario accede.
- Sin tema configurado: generic.light o material.blue.light.
- Al cambiar empresa: cargar tema de esa empresa; sin flash de estilos.
- Selector en administración empresas (edición). Solo usuarios con permiso.
- Persistencia en PQ_Empresa.Theme.

### Escenarios Gherkin

```gherkin
Feature: Selección de apariencia por empresa

  Scenario: Administrador asigna tema a empresa
    Given el administrador está autenticado
    And accede a Administración de empresas
    When edita una empresa
    And selecciona tema "material.blue.light" en el selector
    And guarda el formulario
    Then el tema se persiste en PQ_Empresa.Theme
    And al acceder con esa empresa el tema se aplica

  Scenario: Usuario cambia empresa y ve nuevo tema
    Given el usuario está autenticado
    And la empresa activa tiene theme "generic.light"
    When cambia a otra empresa con theme "material.blue.dark"
    Then el ThemeLoader carga el CSS del nuevo tema
    And la UI se actualiza sin flash de estilos incorrectos

  Scenario: Empresa sin tema configurado
    Given el usuario está autenticado
    And la empresa activa no tiene theme configurado (null)
    When accede a la aplicación
    Then se aplica fallback (generic.light o material.blue.light)
```

---

## 3) Reglas de Negocio

- Apariencia por empresa. Solo temas lista cerrada. Fallback si tema no existe. Tema al contexto empresa activa.

---

## 4) Impacto en Datos

- PQ_Empresa: columna Theme (varchar(50), nullable). Migración agregar columna.
- Lista cerrada: generic.light, generic.dark, material.blue.light, material.blue.dark, etc.

---

## 5) Contratos de API

- GET/PUT empresas: incluir theme en payload. Validación: theme en lista cerrada o null.
- Extiende endpoints existentes de HU-011.

---

## 6) Cambios Frontend

- ThemeLoader: cargar CSS dinámicamente según theme empresa activa. Reemplazar link en head.
- Selector apariencia en formulario edición empresa (dropdown temas).
- Integrar ThemeLoader en flujo empresa activa; aplicar antes de montar layout.
- data-testid: appearance.selector, appearance.selector.option.{themeName}

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | DB | Migración: columna Theme en PQ_Empresa | Nullable, rollback | HU-011 |
| T2 | Backend | Incluir theme en GET/PUT empresas; validación lista cerrada | Persistencia | HU-011, T1 |
| T3 | Frontend | ThemeLoader: cargar CSS según theme empresa activa | Fallback generic.light | HU-011 |
| T4 | Frontend | Integrar ThemeLoader en flujo empresa activa | Sin flash al cambiar | T3, HU-002 |
| T5 | Frontend | Selector apariencia en formulario edición empresa | Dropdown, guardar con formulario | HU-011, T2 |
| T6 | Tests | Unit: validación theme, ThemeLoader fallback | Tests pasan | T3 |
| T7 | Tests | E2E: asignar tema en admin, cambiar empresa, verificar tema | Playwright | T4, T5 |

---

## Bloqueo

**No ejecutar hasta que HU-011 (Administración de empresas) esté completada.**

---

## Archivos creados/modificados

<!-- Completar al ejecutar -->

## Comandos ejecutados

<!-- Completar al ejecutar -->

## Notas y decisiones

<!-- Completar al ejecutar -->

## Pendientes / follow-ups

<!-- Completar al ejecutar -->
