# Documentación: docs-paqsystems/db-design/articulos.md

## Propósito

Documenta el diseño y scripts CREATE de las tablas del módulo de artículos: STA11, CPA14, GVA41, MEDIDA, STA32, STA33.

## Ubicación

`docs-paqsystems/db-design/articulos.md`

## Contenido Principal

1. **Dependencias previas:** UDT necesarios (D_ID, D_CODIGO, D_DESCRIPCION, etc.)
2. **Tipos de usuario (CREATE TYPE):** D_ID, D_CODIGO, D_DESCRIPCION, D_CANT_DECIMAL_ARTIC_UNI_ADIC, D_EQUIVALENCIA_UM, ENTERO_TG, DECIMAL_TG, ENTEROXL_TG
3. **Secuencias:** Una por cada tabla
4. **Mapeo UDT a tipos base:** Tabla de correspondencia
5. **Diagrama ER Mermaid:** STA11 como núcleo, relaciones con CPA14, GVA41, MEDIDA, STA32, STA33
6. **Resumen de tablas:** PK y descripción de cada tabla
7. **Diagrama ASCII:** Vista de STA11 y sus dependencias
8. **Scripts CREATE:** Scripts completos documentados (no modificar)

## Tablas incluidas

| Tabla | Rol |
|-------|-----|
| STA11 | Maestro de artículos (núcleo) |
| CPA14 | Impuestos IVA compras |
| GVA41 | Alícuotas/impuestos ventas |
| MEDIDA | Unidades de medida |
| STA32 | Escalas |
| STA33 | Valores de escala |

## Nota

Los scripts CREATE son los originales obtenidos desde SQL Server y no deben modificarse.
