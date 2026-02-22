/**
 * Tests unitarios: task.service (getTasks, getTask, updateTask, deleteTask, getAllTasks, bulkToggleClose)
 *
 * @see TR-033(MH)-visualización-de-lista-de-tareas-propias.md
 * @see TR-029(MH)-edición-de-tarea-propia.md
 * @see TR-030(MH)-eliminación-de-tarea-propia.md
 * @see TR-031(MH)-edición-de-tarea-supervisor.md
 * @see TR-040(SH)-filtrado-de-tareas-para-proceso-masivo.md
 * @see TR-042(SH)-procesamiento-masivo-de-tareas-cerrarreabrir.md
 * @see TR-043(SH)-validación-de-selección-para-procesamiento.md
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { getToken } from '../../../shared/utils/tokenStorage';
import {
  getTasks,
  getTask,
  updateTask,
  deleteTask,
  getAllTasks,
  bulkToggleClose,
  getDetailReport,
  getReportByClient,
} from './task.service';

const mockToken = 'test-token';

vi.mock('../../../shared/utils/tokenStorage', () => ({
  getToken: vi.fn(() => mockToken),
}));

vi.mock('../../../shared/i18n', () => ({
  t: (key: string, fallback: string) => fallback,
}));

describe('task.service', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe('getTask', () => {
    it('retorna tarea cuando la API responde 200', async () => {
      const taskData = {
        id: 1,
        usuario_id: 1,
        usuario_nombre: 'Juan Pérez',
        cliente_id: 1,
        tipo_tarea_id: 2,
        fecha: '2026-01-28',
        duracion_minutos: 120,
        sin_cargo: false,
        presencial: true,
        observacion: 'Tarea de prueba',
        cerrado: false,
      };
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ error: 0, respuesta: 'OK', resultado: taskData }),
      });

      const result = await getTask(1);

      expect(result.success).toBe(true);
      expect(result.task).toEqual(taskData);
      expect(fetch).toHaveBeenCalledWith(
        expect.stringContaining('/api/v1/tasks/1'),
        expect.objectContaining({ method: 'GET', headers: expect.objectContaining({ Authorization: `Bearer ${mockToken}` }) })
      );
    });

    it('retorna error cuando la API responde 404', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () => Promise.resolve({ error: 4040, respuesta: 'Tarea no encontrada', resultado: {} }),
      });

      const result = await getTask(999);

      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4040);
      expect(result.errorMessage).toBe('Tarea no encontrada');
    });

    it('retorna error cuando la API responde 2110 (tarea cerrada)', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () =>
          Promise.resolve({
            error: 2110,
            respuesta: 'No se puede modificar una tarea cerrada',
            resultado: {},
          }),
      });

      const result = await getTask(1);

      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(2110);
      expect(result.errorMessage).toContain('cerrada');
    });

    it('retorna error cuando la API responde 4030 (sin permisos)', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () =>
          Promise.resolve({
            error: 4030,
            respuesta: 'No tiene permisos para acceder a esta tarea',
            resultado: {},
          }),
      });

      const result = await getTask(1);

      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4030);
      expect(result.errorMessage).toContain('permisos');
    });
  });

  describe('updateTask', () => {
    it('retorna tarea actualizada cuando la API responde 200', async () => {
      const payload = {
        fecha: '2026-01-29',
        cliente_id: 1,
        tipo_tarea_id: 2,
        duracion_minutos: 180,
        sin_cargo: true,
        presencial: false,
        observacion: 'Actualizado',
      };
      const taskData = {
        id: 1,
        usuario_id: 1,
        cliente_id: 1,
        tipo_tarea_id: 2,
        fecha: '2026-01-29',
        duracion_minutos: 180,
        sin_cargo: true,
        presencial: false,
        observacion: 'Actualizado',
        cerrado: false,
        created_at: '2026-01-28T10:00:00Z',
        updated_at: '2026-01-28T15:00:00Z',
      };
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ error: 0, respuesta: 'Tarea actualizada correctamente', resultado: taskData }),
      });

      const result = await updateTask(1, payload);

      expect(result.success).toBe(true);
      expect(result.task).toEqual(taskData);
      expect(fetch).toHaveBeenCalledWith(
        expect.stringContaining('/api/v1/tasks/1'),
        expect.objectContaining({
          method: 'PUT',
          body: JSON.stringify(payload),
          headers: expect.objectContaining({ Authorization: `Bearer ${mockToken}` }),
        })
      );
    });

    it('retorna error cuando la API responde 2110 (tarea cerrada)', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () =>
          Promise.resolve({
            error: 2110,
            respuesta: 'No se puede modificar una tarea cerrada',
            resultado: {},
          }),
      });

      const result = await updateTask(1, {
        fecha: '2026-01-29',
        cliente_id: 1,
        tipo_tarea_id: 2,
        duracion_minutos: 120,
        observacion: 'Test',
      });

      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(2110);
    });

    it('retorna error y validationErrors cuando la API responde 422', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () =>
          Promise.resolve({
            error: 4220,
            respuesta: 'Errores de validación',
            resultado: { errors: { duracion_minutos: ['La duración debe ser múltiplo de 15 minutos.'] } },
          }),
      });

      const result = await updateTask(1, {
        fecha: '2026-01-29',
        cliente_id: 1,
        tipo_tarea_id: 2,
        duracion_minutos: 25,
        observacion: 'Test',
      });

      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4220);
      expect(result.validationErrors).toEqual({ duracion_minutos: ['La duración debe ser múltiplo de 15 minutos.'] });
    });

    it('TR-031: envía usuario_id en payload y retorna 200 (supervisor cambia propietario)', async () => {
      const payload = {
        fecha: '2026-01-29',
        cliente_id: 1,
        tipo_tarea_id: 2,
        duracion_minutos: 180,
        observacion: 'Reasignada',
        usuario_id: 2,
      };
      const taskData = {
        id: 1,
        usuario_id: 2,
        cliente_id: 1,
        tipo_tarea_id: 2,
        fecha: '2026-01-29',
        duracion_minutos: 180,
        sin_cargo: false,
        presencial: false,
        observacion: 'Reasignada',
        cerrado: false,
        updated_at: '2026-01-28T15:00:00Z',
      };
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ error: 0, respuesta: 'Tarea actualizada correctamente', resultado: taskData }),
      });

      const result = await updateTask(1, payload);

      expect(result.success).toBe(true);
      expect(result.task?.usuario_id).toBe(2);
      expect(fetch).toHaveBeenCalledWith(
        expect.stringContaining('/api/v1/tasks/1'),
        expect.objectContaining({
          method: 'PUT',
          body: JSON.stringify(payload),
        })
      );
    });

    it('retorna error 4030 cuando la API responde 403 (solo supervisores pueden cambiar propietario)', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () =>
          Promise.resolve({
            error: 4030,
            respuesta: 'Solo los supervisores pueden cambiar el propietario de una tarea',
            resultado: {},
          }),
      });

      const result = await updateTask(1, {
        fecha: '2026-01-29',
        cliente_id: 1,
        tipo_tarea_id: 2,
        duracion_minutos: 120,
        observacion: 'Test',
        usuario_id: 2,
      });

      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4030);
    });
  });

  describe('deleteTask', () => {
    it('retorna success cuando la API responde 200', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({ error: 0, respuesta: 'Tarea eliminada correctamente', resultado: {} }),
      });

      const result = await deleteTask(1);

      expect(result.success).toBe(true);
      expect(fetch).toHaveBeenCalledWith(
        expect.stringContaining('/api/v1/tasks/1'),
        expect.objectContaining({
          method: 'DELETE',
          headers: expect.objectContaining({ Authorization: `Bearer ${mockToken}` }),
        })
      );
    });

    it('retorna error cuando la API responde 404', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () => Promise.resolve({ error: 4040, respuesta: 'Tarea no encontrada', resultado: {} }),
      });

      const result = await deleteTask(999);

      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4040);
      expect(result.errorMessage).toBe('Tarea no encontrada');
    });

    it('retorna error cuando la API responde 2111 (tarea cerrada)', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () =>
          Promise.resolve({
            error: 2111,
            respuesta: 'No se puede eliminar una tarea cerrada',
            resultado: {},
          }),
      });

      const result = await deleteTask(1);

      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(2111);
      expect(result.errorMessage).toContain('cerrada');
    });

    it('retorna error cuando la API responde 4030 (sin permisos)', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () =>
          Promise.resolve({
            error: 4030,
            respuesta: 'No tiene permisos para eliminar esta tarea',
            resultado: {},
          }),
      });

      const result = await deleteTask(1);

      expect(result.success).toBe(false);
      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4030);
      expect(result.errorMessage).toContain('eliminar');
    });
  });

  describe('getDetailReport (TR-044)', () => {
    it('retorna data, pagination y totalHoras cuando la API responde 200', async () => {
      const apiResult = {
        data: [
          {
            id: 1,
            cliente: { id: 1, nombre: 'Cliente A', tipo_cliente: 'Corp' },
            fecha: '2026-01-28',
            tipo_tarea: { id: 2, descripcion: 'Desarrollo' },
            horas: 2.5,
            sin_cargo: false,
            presencial: true,
            descripcion: 'Tarea consulta',
          },
        ],
        pagination: { current_page: 1, per_page: 15, total: 1, last_page: 1 },
        total_horas: 2.5,
      };
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () =>
          Promise.resolve({
            error: 0,
            respuesta: 'Consulta obtenida correctamente',
            resultado: apiResult,
          }),
      });

      const result = await getDetailReport({
        fecha_desde: '2026-01-01',
        fecha_hasta: '2026-01-31',
      });

      expect(result.success).toBe(true);
      expect(result.data).toEqual(apiResult.data);
      expect(result.pagination).toEqual(apiResult.pagination);
      expect(result.totalHoras).toBe(2.5);
      expect(fetch).toHaveBeenCalledWith(
        expect.stringContaining('/api/v1/reports/detail'),
        expect.objectContaining({
          method: 'GET',
          headers: expect.objectContaining({ Authorization: `Bearer ${mockToken}` }),
        })
      );
    });

    it('retorna error 1305 cuando período inválido (422)', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () =>
          Promise.resolve({
            error: 1305,
            respuesta:
              'El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta',
            resultado: null,
          }),
      });

      const result = await getDetailReport({
        fecha_desde: '2026-01-31',
        fecha_hasta: '2026-01-01',
      });

      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(1305);
      expect(result.errorMessage).toContain('período');
    });

    it('retorna error cuando no hay token', async () => {
      vi.mocked(getToken).mockReturnValueOnce(null);
      const result = await getDetailReport({});
      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4001);
    });
  });

  describe('getReportByClient (TR-046)', () => {
    it('retorna grupos, totalGeneralHoras y totalGeneralTareas cuando la API responde 200', async () => {
      const apiResult = {
        grupos: [
          {
            cliente_id: 1,
            nombre: 'Cliente A',
            tipo_cliente: { id: 1, descripcion: 'Corp' },
            total_horas: 10.5,
            cantidad_tareas: 5,
            tareas: [
              {
                id: 1,
                fecha: '2026-01-28',
                tipo_tarea: { id: 2, descripcion: 'Desarrollo' },
                horas: 2.0,
                descripcion: 'Tarea 1',
              },
            ],
          },
        ],
        total_general_horas: 10.5,
        total_general_tareas: 5,
      };
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () =>
          Promise.resolve({
            error: 0,
            respuesta: 'Reporte por cliente obtenido correctamente',
            resultado: apiResult,
          }),
      });

      const result = await getReportByClient({
        fecha_desde: '2026-01-01',
        fecha_hasta: '2026-01-31',
      });

      expect(result.success).toBe(true);
      expect(result.grupos).toEqual(apiResult.grupos);
      expect(result.totalGeneralHoras).toBe(10.5);
      expect(result.totalGeneralTareas).toBe(5);
      expect(fetch).toHaveBeenCalledWith(
        expect.stringContaining('/api/v1/reports/by-client'),
        expect.objectContaining({
          method: 'GET',
          headers: expect.objectContaining({ Authorization: `Bearer ${mockToken}` }),
        })
      );
    });

    it('retorna error 1305 cuando período inválido (422)', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        json: () =>
          Promise.resolve({
            error: 1305,
            respuesta:
              'El período es inválido: fecha_desde debe ser menor o igual a fecha_hasta',
            resultado: null,
          }),
      });

      const result = await getReportByClient({
        fecha_desde: '2026-01-31',
        fecha_hasta: '2026-01-01',
      });

      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(1305);
    });

    it('retorna error cuando no hay token', async () => {
      vi.mocked(getToken).mockReturnValueOnce(null);
      const result = await getReportByClient({});
      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4001);
    });
  });

  describe('getAllTasks — TR-034, TR-040', () => {
    it('incluye parámetro cerrado en la URL cuando se pasa cerrado: true', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () =>
          Promise.resolve({
            error: 0,
            respuesta: 'OK',
            resultado: {
              data: [],
              pagination: { current_page: 1, per_page: 15, total: 0, last_page: 1 },
              totales: { cantidad_tareas: 0, total_horas: 0 },
            },
          }),
      });
      await getAllTasks({ cerrado: true });
      expect(fetch).toHaveBeenCalledWith(
        expect.stringContaining('/api/v1/tasks/all'),
        expect.any(Object)
      );
      const url = (fetch as ReturnType<typeof vi.fn>).mock.calls[0][0];
      expect(url).toContain('cerrado=true');
    });

    it('retorna error 1305 cuando el backend responde rango de fechas inválido', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 422,
        json: () =>
          Promise.resolve({
            error: 1305,
            respuesta: 'La fecha desde no puede ser posterior a fecha hasta',
            resultado: {},
          }),
      });
      const result = await getAllTasks({ fecha_desde: '2026-02-01', fecha_hasta: '2026-01-01' });
      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(1305);
    });

    it('retorna error 403 cuando el usuario no es supervisor', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 403,
        json: () =>
          Promise.resolve({
            error: 4030,
            respuesta: 'Solo los supervisores pueden acceder a todas las tareas',
            resultado: {},
          }),
      });
      const result = await getAllTasks({});
      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4030);
    });
  });

  describe('bulkToggleClose — TR-042, TR-043', () => {
    it('retorna processed cuando la API responde 200', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () =>
          Promise.resolve({
            error: 0,
            respuesta: 'Se procesaron 2 registros',
            resultado: { processed: 2, task_ids: [1, 2] },
          }),
      });
      const result = await bulkToggleClose([1, 2]);
      expect(result.success).toBe(true);
      expect(result.processed).toBe(2);
      expect(fetch).toHaveBeenCalledWith(
        expect.stringContaining('/api/v1/tasks/bulk-toggle-close'),
        expect.objectContaining({
          method: 'POST',
          body: JSON.stringify({ task_ids: [1, 2] }),
        })
      );
    });

    it('retorna error 1212 cuando task_ids está vacío (validación frontend)', async () => {
      const result = await bulkToggleClose([]);
      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(1212);
      expect(result.errorMessage).toContain('Debe seleccionar al menos una tarea');
      expect(fetch).not.toHaveBeenCalled();
    });

    it('retorna error 1212 cuando el backend responde selección vacía', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 422,
        json: () =>
          Promise.resolve({
            error: 1212,
            respuesta: 'Debe seleccionar al menos una tarea',
            resultado: {},
          }),
      });
      const result = await bulkToggleClose([1]);
      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(1212);
    });

    it('retorna error 403 cuando el usuario no es supervisor', async () => {
      globalThis.fetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 403,
        json: () =>
          Promise.resolve({
            error: 4030,
            respuesta: 'Solo los supervisores pueden ejecutar el proceso masivo',
            resultado: {},
          }),
      });
      const result = await bulkToggleClose([1, 2]);
      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4030);
    });

    it('retorna error cuando no hay token', async () => {
      vi.mocked(getToken).mockReturnValueOnce(null);
      const result = await bulkToggleClose([1]);
      expect(result.success).toBe(false);
      expect(result.errorCode).toBe(4001);
    });
  });
});
