/**
 * Utilidad: exportToExcel (TR-049)
 *
 * Genera archivos XLSX a partir de datos de consultas (detallada o agrupada)
 * y dispara la descarga en el navegador.
 */

import * as XLSX from 'xlsx';
import type { DetailReportItem } from '../services/task.service';

/**
 * Genera nombre de archivo descriptivo con período.
 * @param fechaDesde Fecha desde (YYYY-MM-DD)
 * @param fechaHasta Fecha hasta (YYYY-MM-DD)
 * @param suffix Sufijo opcional (ej. "por-cliente")
 */
export function buildExportFileName(
  fechaDesde: string,
  fechaHasta: string,
  suffix?: string
): string {
  const desde = fechaDesde || 'inicio';
  const hasta = fechaHasta || 'fin';
  const name = suffix ? `Tareas_${desde}_${hasta}_${suffix}` : `Tareas_${desde}_${hasta}`;
  return `${name}.xlsx`;
}

/**
 * Exporta datos de consulta detallada a un libro XLSX y descarga.
 * @param data Lista de ítems del reporte detallado
 * @param filename Nombre del archivo (ej. Tareas_2026-01-01_2026-01-31.xlsx)
 * @param includeEmpleado Si true, incluye columna Empleado
 */
export function exportDetailToExcel(
  data: DetailReportItem[],
  filename: string,
  includeEmpleado: boolean = false
): void {
  const headers = includeEmpleado
    ? ['Empleado', 'Cliente', 'Fecha', 'Tipo tarea', 'Horas', 'Sin cargo', 'Presencial', 'Descripción']
    : ['Cliente', 'Fecha', 'Tipo tarea', 'Horas', 'Sin cargo', 'Presencial', 'Descripción'];

  const rows = data.map((row) => {
    const cliente = row.cliente.nombre + (row.cliente.tipo_cliente ? ` (${row.cliente.tipo_cliente})` : '');
    const base = [
      cliente,
      row.fecha,
      row.tipo_tarea.descripcion,
      row.horas,
      row.sin_cargo ? 'Sí' : 'No',
      row.presencial ? 'Sí' : 'No',
      row.descripcion ?? '',
    ];
    if (includeEmpleado) {
      return [row.empleado?.nombre ?? '—', ...base];
    }
    return base;
  });

  const sheetData = [headers, ...rows];
  const ws = XLSX.utils.aoa_to_sheet(sheetData);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Tareas');
  XLSX.writeFile(wb, filename);
}

/**
 * Fila de detalle para reportes agrupados (común a todos los grupos)
 */
export interface GroupedExportTaskRow {
  fecha: string;
  cliente: string;
  tipoTarea: string;
  horas: number;
  sinCargo: boolean;
  presencial: boolean;
  descripcion: string;
}

/**
 * Grupo para exportación agrupada (un título + totales + filas de detalle)
 */
export interface GroupedExportGroup {
  groupTitle: string;
  totalHoras: number;
  cantidadTareas: number;
  tareas: GroupedExportTaskRow[];
}

/**
 * Exporta datos de consulta agrupada a un libro XLSX y descarga.
 * Una hoja con secciones por grupo: fila de resumen (título, total horas, cantidad) y tabla de detalle.
 * @param groups Lista de grupos con título, totales y tareas
 * @param filename Nombre del archivo
 */
export function exportGroupedToExcel(
  groups: GroupedExportGroup[],
  filename: string
): void {
  const detailHeaders = ['Fecha', 'Cliente', 'Tipo tarea', 'Horas', 'Sin cargo', 'Presencial', 'Descripción'];
  const rows: (string | number)[][] = [];

  for (const g of groups) {
    rows.push([g.groupTitle, g.totalHoras, g.cantidadTareas]);
    rows.push(detailHeaders);
    for (const t of g.tareas) {
      rows.push([
        t.fecha,
        t.cliente,
        t.tipoTarea,
        t.horas,
        t.sinCargo ? 'Sí' : 'No',
        t.presencial ? 'Sí' : 'No',
        t.descripcion ?? '',
      ]);
    }
    rows.push([]);
  }

  const ws = XLSX.utils.aoa_to_sheet(rows);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Tareas');
  XLSX.writeFile(wb, filename);
}
