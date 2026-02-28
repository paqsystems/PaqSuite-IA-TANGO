---
alwaysApply: true
---
# description: Contexto del Proyecto – ERP PaqSuite

## Propósito

Este documento proporciona el contexto general del proyecto para que el agente IA comprenda el dominio, objetivos y restricciones.

## Contexto General

Este proyecto es una **plataforma ERP** en evolución. Actualmente incluye:

- **Módulo Sistema de Partes (MVP):** Registro de tareas diarias por empleados (cliente, tipo de tarea, duración) para informes de dedicación.
- **Visión ERP:** Modelo multiempresa, multiusuario, multirrol; separación Dictionary DB / Company DB; arquitectura por capas.

## Objetivo Actual (MVP Partes)

- Registrar tareas diarias de forma simple
- Asociar tareas a clientes y tipos de trabajo
- Visualizar la dedicación por usuario y cliente
- Entregar un flujo E2E completo y funcional

## Qué SÍ es este proyecto

- Una plataforma ERP con módulo de Partes operativo
- Modelo multiempresa (Dictionary DB + Company DB por empresa)
- Autenticación y autorización por roles/permisos
- Una aplicación web con backend (Laravel), frontend (React), tests y deploy

## Qué NO es este proyecto (en el MVP actual)

- No es una plataforma de automatización
- No incluye facturación ni reportes avanzados en el MVP de Partes
- No incluye módulos comerciales/contables completos (en desarrollo)

## Usuarios

- sòlo se distinguirà un usuario de nivel Supervisor por el rol d"supervisor que permite realizar todas las tareas.
- El resto de alcances y limitaciones se definiràn por permisos de acco.

## Flujo E2E Prioritario

**Login → Registro de tarea diaria → Visualización de tareas / resumen**

Todo el desarrollo debe soportar este flujo.

## Entidades Clave

a nivel "diccionario":
- Usuario, roles, empresa, permisos, opciòn de menù
- Modelo multiempresa: tenant vía header `X-Company-Id`
- grupos empresarios, empresas que comprende cada grupo
- tareas programadas, frecuencia, procesos

## Principios de Diseño

- Simplicidad sobre sofisticación
- No sobre–ingeniería
- Validaciones claras
- Trazabilidad del trabajo
- Testing enfocado en el flujo principal

## Alcance Técnico

- Arquitectura por capas (Controller → Service → Domain → Repository)
- API REST (`/api/v1/`)
- Autenticación Sanctum
- Tests unitarios, integración y E2E

## Referencias

- `docs/00-contexto/00-contexto-global-erp.md` - Contexto global del ERP
- `docs/01-arquitectura/README.md` - Documentación de arquitectura
- `docs/_projects/SistemaPartes/` - Historias y tareas del módulo Partes
