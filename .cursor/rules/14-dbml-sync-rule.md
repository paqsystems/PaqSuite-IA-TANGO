---
alwaysApply: true
---
# description: Regla de Sincronización DBML - Modelo de Datos

## Objetivo

Mantener el archivo `database/modelo-datos.dbml` sincronizado con `docs/modelo-datos.md` para garantizar que la visualización gráfica del modelo de datos refleje siempre la documentación actualizada.

## Alcance

Esta regla aplica a:
- Modificaciones en `docs/modelo-datos.md`
- Cambios en entidades, relaciones o restricciones del modelo de datos
- Actualización del archivo `.dbml` para dbdiagram.io

## Regla Obligatoria

**SIEMPRE que se modifique `docs/modelo-datos.md`, se DEBE actualizar:**
1. `database/modelo-datos.dbml` - Archivo DBML para dbdiagram.io
2. Diagrama Mermaid en `docs/modelo-datos.md` - Para visualización en Cursor

**Nota:** El diagrama Mermaid se actualiza manualmente, pero debe reflejar las mismas relaciones que el DBML.

### Proceso de Actualización

1. **Detectar cambios en `docs/modelo-datos.md`:**
   - Nuevas entidades
   - Nuevos campos en entidades existentes
   - Cambios en tipos de datos
   - Nuevas relaciones o cambios en relaciones
   - Nuevas restricciones

2. **Actualizar `database/modelo-datos.dbml`:**
   - Agregar/modificar tablas según cambios en modelo-datos.md
   - Actualizar referencias (FK) según relaciones documentadas
   - Mantener comentarios descriptivos (notes) alineados con la documentación
   - Actualizar fecha en comentario de "Última actualización"

3. **Verificar consistencia:**
   - Todas las entidades de modelo-datos.md deben estar en .dbml
   - Todas las relaciones documentadas deben estar reflejadas
   - Tipos de datos deben ser consistentes

## Mapeo de Tipos de Datos

Al convertir de modelo-datos.md a .dbml, usar:

| Modelo-datos.md | DBML |
|----------------|------|
| id (PK) | `int [pk]` |
| code (único) | `varchar [unique]` |
| nombre | `varchar` |
| email (único) | `varchar [unique]` |
| password_hash | `varchar` |
| boolean | `boolean` |
| boolean (default: false) | `boolean [default: false]` |
| created_at | `timestamp` |
| updated_at | `timestamp` |
| fecha | `date` |
| duracion_minutos | `int` |
| observacion | `text` |
| descripcion | `varchar` |
| FK → Tabla | `int [ref: > Tabla.id]` |

## Convenciones DBML

1. **Nombres de tablas:** PascalCase (Usuario, Cliente, RegistroTarea)
2. **Nombres de campos:** snake_case (usuario_id, tipo_cliente_id)
3. **Comentarios:** Usar `note:` para descripciones de campos
4. **Restricciones NOT NULL:** Usar `[not null]` para campos obligatorios
5. **Valores por defecto:** Usar `[default: valor]`
6. **Foreign Keys:** Usar `[ref: > Tabla.id]` o `[ref: < Tabla.id]` según cardinalidad

## Ejemplo de Actualización

**Antes (modelo-datos.md):**
```markdown
### Usuario
- id (PK)
- code (único)
- nombre
```

**Después (agregar campo):**
```markdown
### Usuario
- id (PK)
- code (único)
- nombre
- telefono (opcional)
```

**Acción requerida en .dbml:**
```dbml
Table Usuario {
  id int [pk]
  code varchar [unique]
  nombre varchar
  telefono varchar [note: 'Teléfono del usuario (opcional)']
}
```

## Checklist de Sincronización

Al modificar `docs/modelo-datos.md`, verificar:

- [ ] ¿Se agregó/modificó/eliminó alguna entidad? → Actualizar tabla en .dbml
- [ ] ¿Se agregaron/modificaron campos? → Actualizar campos en tabla correspondiente
- [ ] ¿Cambiaron las relaciones? → Actualizar referencias `[ref:]` en .dbml
- [ ] ¿Se agregaron restricciones? → Actualizar `[not null]`, `[default:]`, etc.
- [ ] ¿Se actualizó la fecha de última actualización en comentario del .dbml?

## Herramientas

- **dbdiagram.io:** Importar `database/modelo-datos.dbml` para visualización gráfica
- **Exportación:** Desde dbdiagram.io se puede exportar a PNG, PDF, SQL, etc.

## Referencias

- Documentación del modelo: `docs/modelo-datos.md`
- Archivo DBML: `database/modelo-datos.dbml`
- Sintaxis DBML: https://dbdiagram.io/docs
