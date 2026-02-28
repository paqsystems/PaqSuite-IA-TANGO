# 24 — Estándar de Grillas DevExtreme (DataGrid)

## 0) Propósito

Esta regla define el estándar obligatorio para todas las grillas (DataGrid) del proyecto. Aprovecha las capacidades nativas de DevExtreme para ofrecer una experiencia consistente y completa en listados.

---

## 1) Características Obligatorias

Toda grilla del proyecto debe incluir las siguientes características.

### 1.1 Acciones CRUD por fila

- **Botón Agregar:** En toolbar o zona superior, para crear un nuevo registro.
- **Botón Editar:** En cada fila (columna de acciones o icono).
- **Botón Eliminar:** En cada fila (columna de acciones o icono).

Según el caso de uso, algunos botones pueden ocultarse por permisos.

### 1.2 Columnas

- **Todas las columnas** del juego de datos deben estar disponibles.
- **Algunas visibles por defecto**, otras ocultas.
- **Column Chooser:** Botón para mostrar/ocultar columnas disponibles (agregar a la vista).
- Usar `columnChooser` de DevExtreme DataGrid.

### 1.3 Ordenamiento

- **Ordenar por cualquier columna:** `allowSorting={true}` (por defecto).
- Clic en encabezado para ordenar ascendente/descendente.

### 1.4 Filtrado

- **Filtrar por cualquier columna:** `filterRow={{ visible: true }}`.
- Fila de filtros siempre visible debajo de los encabezados.

### 1.5 Reordenación de columnas

- **Permitir arrastrar columnas:** `allowColumnReordering={true}`.
- El usuario puede reubicar columnas arrastrando el encabezado.

### 1.6 Fila de filtros

- **Habilitada siempre:** `filterRow={{ visible: true }}`.

### 1.7 Agrupación

- **Panel de agrupación habilitado:** `groupPanel={{ visible: true }}`.
- El usuario puede arrastrar columnas al panel para agrupar registros.
- `grouping={{ autoExpandAll: false }}` (o true según preferencia).

### 1.8 Pie con totalizadores

- **Summary habilitado:** `summary` con `totalItems` para totales.
- Soportar: suma, conteo, promedio, min, max según tipo de columna.
- Usar `Summary` component con `summaryType="sum" | "count" | "avg" | "min" | "max"`.

### 1.9 Columna de selección

- **Checkbox de selección siempre habilitado:** `selection={{ mode: 'multiple' }}` o `mode: 'multiple'` según API.
- **Seleccionar todos:** Checkbox en encabezado.
- **Seleccionar por grupo:** Si hay agrupación, permitir seleccionar todos los registros de un grupo (comportamiento nativo de DevExtreme cuando aplica).

### 1.10 Layouts persistentes (cuando se implemente)

- Ver HU en `docs/03-historias-usuario/000-Generalidades/HU-001-layouts-grilla.md` para guardar/cargar formatos.
- Cada grilla debe identificar: `proceso` (de `pq_menus.procedimiento`) y `grid_id` (si hay varias grillas en la pantalla).

---

## 2) Identificación de Grillas

Para el almacenamiento de layouts (tabla `pq_grid_layouts`):

| Propiedad | Origen | Ejemplo |
|-----------|--------|---------|
| `proceso` | `pq_menus.procedimiento` | "Clientes", "Empleados", "TiposTarea" |
| `grid_id` | Identificador cuando hay varias grillas en la misma pantalla | "default", "master", "detalle", "lista-tareas" |

Si la pantalla tiene una sola grilla, usar `grid_id = "default"`.

---

## 3) Configuración de Referencia (DataGrid DevExtreme)

```tsx
<DataGrid
  dataSource={dataSource}
  showBorders={true}
  allowColumnReordering={true}
  allowColumnResizing={true}
  columnAutoWidth={true}
  filterRow={{ visible: true }}
  groupPanel={{ visible: true }}
  selection={{ mode: 'multiple', showCheckBoxesMode: 'always' }}
  headerFilter={{ visible: true }}
  columnChooser={{ mode: 'select' }}
  searchPanel={{ visible: true }}
  data-testid="grid.{proceso}.{grid_id}"
>
  <ColumnChooser mode="select" />
  <FilterRow visible={true} />
  <GroupPanel visible={true} />
  <Selection mode="multiple" showCheckBoxesMode="always" />
  <HeaderFilter visible={true} />
  <SearchPanel visible={true} />
  <Paging defaultPageSize={20} />
  <Pager visible={true} showPageSizeSelector={true} />
  <Summary>
    <TotalItem column="campoNumerico" summaryType="sum" />
    <TotalItem column="id" summaryType="count" />
  </Summary>
  {/* Columnas según dominio */}
</DataGrid>
```

Ajustar según la API exacta de DevExtreme React (puede variar por versión).

---

## 4) data-testid

Formato obligatorio para grillas:

```
grid.{proceso}.{grid_id}
```

Ejemplo: `grid.clientes.default`, `grid.tareas.master`.

---

## 5) Referencias

- **DevExtreme DataGrid:** [Documentación oficial](https://js.devexpress.com/Documentation/ApiReference/UI_Components/dxDataGrid/)
- **Layouts:** `docs/03-historias-usuario/000-Generalidades/HU-001-layouts-grilla.md`
- **Tabla:** `docs/modelo-datos/md-diccionario/md-diccionario.md` – `pq_grid_layouts`
- **UI Layer:** `docs/frontend/ui-layer-wrappers.md`, `docs/frontend/devextreme-norms.md`
