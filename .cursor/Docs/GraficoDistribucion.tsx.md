# Documentación: GraficoDistribucion.tsx

## Ubicación
`frontend/src/app/GraficoDistribucion.tsx`

## Propósito
Componente de gráfico de barras (Recharts) para TR-054: distribución de horas en el dashboard. Recibe datos { name, value }[] y muestra un BarChart responsive con colores accesibles.

## Uso
- Dashboard: gráfico por cliente (empleado/supervisor), por empleado (supervisor), por tipo de tarea (cliente).
- Props: datos, titulo, dataTestId, ariaLabel.

## Dependencias
- recharts (BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell).
