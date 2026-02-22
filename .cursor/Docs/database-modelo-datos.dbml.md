# Documentación: database/modelo-datos.dbml

## Propósito

Modelo de datos en formato DBML (Database Markup Language) para visualización gráfica del esquema de base de datos del MVP. Compatible con dbdiagram.io.

## Ubicación

`database/modelo-datos.dbml`

## Contenido Principal

- **Tablas definidas:** USERS, PQ_PARTES_TIPOS_CLIENTE, PQ_PARTES_TIPOS_TAREA, PQ_PARTES_USUARIOS, PQ_PARTES_CLIENTES, PQ_PARTES_REGISTRO_TAREA, PQ_PARTES_CLIENTE_TIPO_TAREA
- **Relaciones:** Foreign keys documentadas (User→Usuario, User→Cliente, Usuario→RegistroTarea, etc.)
- **Notas:** Comentarios en cada tabla y campo según docs/modelo-datos.md

## Relación con Otros Documentos

- **Fuente de verdad:** `docs/modelo-datos.md`
- **Regla de sincronización:** `.cursor/rules/14-dbml-sync-rule.md`
- **Migraciones Laravel:** `backend/database/migrations/`

## Uso

1. Abrir https://dbdiagram.io
2. Importar el archivo modelo-datos.dbml
3. Visualizar el diagrama ER interactivo
