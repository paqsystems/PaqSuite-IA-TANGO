/**
 * Component: GraficoDistribucion (TR-054)
 * Gráfico de barras para distribución de horas. Recharts.
 */

import React from 'react';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  Cell,
} from 'recharts';

export interface GraficoDistribucionDato {
  name: string;
  value: number;
}

const COLORS = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#fee140', '#30cfd0'];

interface GraficoDistribucionProps {
  datos: GraficoDistribucionDato[];
  titulo: string;
  dataTestId: string;
  ariaLabel: string;
}

export function GraficoDistribucion({
  datos,
  titulo,
  dataTestId,
  ariaLabel,
}: GraficoDistribucionProps): React.ReactElement {
  if (datos.length === 0) {
    return (
      <section className="dashboard-grafico" data-testid={dataTestId} aria-label={ariaLabel}>
        <h4 className="dashboard-grafico-titulo">{titulo}</h4>
        <p className="dashboard-empty" role="status">No hay datos para mostrar.</p>
      </section>
    );
  }

  return (
    <section className="dashboard-grafico" data-testid={dataTestId} aria-label={ariaLabel}>
      <h4 className="dashboard-grafico-titulo">{titulo}</h4>
      <div className="dashboard-grafico-container">
        <ResponsiveContainer width="100%" height={280}>
          <BarChart data={datos} margin={{ top: 10, right: 10, left: 0, bottom: 60 }}>
            <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
            <XAxis
              dataKey="name"
              tick={{ fontSize: 12 }}
              angle={-35}
              textAnchor="end"
              height={60}
              interval={0}
            />
            <YAxis tick={{ fontSize: 12 }} unit=" h" />
            <Tooltip
              formatter={(value: number | undefined) => [value != null ? `${value} h` : '0 h', 'Horas']}
              labelFormatter={(label) => ` ${label}`}
            />
            <Bar dataKey="value" name="Horas" radius={[4, 4, 0, 0]}>
              {datos.map((_, index) => (
                <Cell key={index} fill={COLORS[index % COLORS.length]} />
              ))}
            </Bar>
          </BarChart>
        </ResponsiveContainer>
      </div>
    </section>
  );
}
