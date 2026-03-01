# TR-004 – Selección de idioma de la aplicación

| Campo              | Valor                                      |
|--------------------|--------------------------------------------|
| HU relacionada     | HU-004 – Selección de idioma               |
| Épica              | 000 – Generalidades                        |
| Prioridad          | SHOULD-HAVE                                |
| Roles              | Usuario (antes y después de login)         |
| Dependencias       | HU-001 (Login)                             |
| Clasificación      | HU SIMPLE                                  |
| Última actualización | 2026-02-27                               |
| Estado             | Implementado                               |

**Origen:** [HU-004 – Selección de idioma](../../03-historias-usuario/000-Generalidades/HU-004-seleccion-idioma.md)

---

## 1) HU Refinada

- **Título:** Selección de idioma de la aplicación
- **Narrativa:** Como usuario quiero elegir el idioma de la aplicación para usarla en mi idioma preferido, disponible desde login y persistente.
- **Contexto:** Selector en login y en header (control dedicado, no en menú usuario). i18n (react-i18next). Persistencia en users.locale.
- **In scope:** Selector login + header, idioma inicial (navegador o guardado), persistencia, i18n activo.
- **Out of scope:** Traducción automática de datos de negocio.

---

## 2) Criterios de Aceptación

- Selector en pantalla login (antes de autenticarse). Selector en header post-login (control independiente, no en menú usuario).
- Idioma inicial: users.locale si existe; si no, navigator.language; si no soportado, default (español).
- Selección: banderas, dropdown o lista. Aplicación inmediata sin recargar.
- Persistencia: users.locale (Dictionary DB). No autenticados: localStorage temporal, al login enviar a backend.
- i18n (react-i18next) activo; textos UI con t(). Idiomas soportados (ej. es, en) con archivos traducción.

### Escenarios Gherkin

```gherkin
Feature: Selección de idioma de la aplicación

  Scenario: Usuario cambia idioma en pantalla de login
    Given el usuario está en la pantalla de login (no autenticado)
    When selecciona idioma "English" en el LanguageSelector
    Then la UI cambia inmediatamente al inglés
    And la preferencia se guarda en localStorage
    When hace login exitosamente
    Then el locale se envía al backend y persiste en users.locale

  Scenario: Usuario cambia idioma post-login
    Given el usuario está autenticado
    And la UI está en español
    When selecciona idioma "English" en el header
    Then la UI cambia inmediatamente al inglés
    And la preferencia se persiste en backend (users.locale)
    And no se recarga la página

  Scenario: Idioma inicial según preferencia guardada
    Given el usuario tiene users.locale = "en" en backend
    When accede a la aplicación (login o post-login)
    Then la UI se muestra en inglés desde el inicio

  Scenario: Idioma inicial sin preferencia (navegador)
    Given el usuario no tiene preferencia guardada
    And navigator.language es "en"
    When accede a la pantalla de login
    Then la UI usa inglés como idioma inicial
```

---

## 3) Reglas de Negocio

- Preferencia por usuario, no por empresa. Solo idiomas con archivos de traducción. Afecta UI (labels, mensajes, formatos); datos de negocio no se traducen.

---

## 4) Impacto en Datos

- Tabla users: campo locale (varchar(10)). Ya existe en modelo.

---

## 5) Contratos de API

- GET/PUT preferencias (locale). Incluir en login response o endpoint /api/users/me/preferences.
- POST login: si usuario envía locale (de localStorage), persistir en users.locale.

---

## 6) Cambios Frontend

- LanguageSelector: componente con banderas o dropdown. Ubicación: login (esquina) + header (barra superior).
- i18n configurado; changeLanguage() al seleccionar. Persistir en backend si autenticado; localStorage si no.
- data-testid: languageSelector, languageSelector.option.es, languageSelector.option.en

---

## 7) Plan de Tareas / Tickets

| ID | Tipo | Descripción | DoD | Deps |
|----|------|-------------|-----|------|
| T1 | Backend | Incluir locale en login response; endpoint PUT preferencias (locale) | Persistencia | - |
| T2 | Frontend | i18n (react-i18next) configurado, archivos es/en | Cambio idioma funcional | - |
| T3 | Frontend | LanguageSelector en pantalla login | Visible, funcional pre-login | - |
| T4 | Frontend | LanguageSelector en header (control dedicado) | Visible post-login | HU-001 |
| T5 | Frontend | Persistencia: localStorage pre-login, backend post-login | Locale persistido | T1, T4 |
| T6 | Tests | Unit: LanguageSelector, i18n changeLanguage | Tests pasan | T2 |
| T7 | Tests | E2E: cambiar idioma en login, verificar persistencia post-login | Playwright | T5 |

---

## Archivos creados/modificados

<!-- Completar al ejecutar -->

## Comandos ejecutados

<!-- Completar al ejecutar -->

## Notas y decisiones

<!-- Completar al ejecutar -->

## Pendientes / follow-ups

<!-- Completar al ejecutar -->
