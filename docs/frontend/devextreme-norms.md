# Normas de uso de DevExtreme en el frontend

## Licencia

Para activar la licencia de DevExtreme:

1. Obtener la clave en [DevExpress Download Manager](https://www.devexpress.com/ClientCenter/DownloadManager/) (DevExtreme Subscription > expandir > copiar license key).
2. Crear el archivo `frontend/src/devextreme-license.ts` (o copiar desde `devextreme-license.ts.example`).
3. Reemplazar `'TU_LICENCIA_AQUI'` por tu clave real.

El archivo `devextreme-license.ts` está en `.gitignore` para que cada desarrollador use su propia clave.

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
