# Normas de uso de DevExtreme en el frontend

## Instalación

```bash
npm install devextreme devextreme-react
```

## Estilos

Importar en `main.tsx`:

```ts
import 'devextreme/dist/css/dx.light.css';
```

Temas disponibles: `dx.light.css`, `dx.dark.css`, `dx.material.blue.light.css`, `dx.fluent.sass.light.css`.

## Componentes equivalentes

| Componente propio | DevExtreme equivalente |
|------------------|-------------------------|
| DataTable        | DataGrid                |
| TextField        | TextBox, DateBox        |
| Button           | Button                  |
| Modal            | Popup, Dialog          |

## Uso de DataGrid

```tsx
import { DataGrid, Column } from 'devextreme-react/data-grid';

<DataGrid
  dataSource={data}
  showBorders={true}
  data-testid="mi-tabla"
>
  <Column dataField="nombre" caption="Nombre" />
  <Column dataField="fecha" caption="Fecha" dataType="date" />
</DataGrid>
```

## TestId y accesibilidad

- Siempre incluir `data-testid` en componentes DevExtreme cuando sea posible.
- Los componentes DevExtreme admiten `className` y atributos HTML estándar.

## Localización

DevExtreme soporta localización vía `loadMessages` y `locale`. Ver documentación oficial para integrar con react-i18next.
