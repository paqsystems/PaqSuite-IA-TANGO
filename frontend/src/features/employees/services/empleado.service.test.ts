/**
 * Tests unitarios: empleado.service (getEmpleados, createEmpleado, getEmpleado, updateEmpleado, deleteEmpleado) — TR-018, TR-019, TR-020, TR-021
 *
 * @see TR-018(MH)-listado-de-empleados.md
 * @see TR-019(MH)-creación-de-empleado.md
 * @see TR-020(MH)-edición-de-empleado.md
 * @see TR-021(MH)-eliminación-de-empleado.md
 * @see TR-022(SH)-visualización-de-detalle-de-empleado.md
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { getToken } from '../../../shared/utils/tokenStorage';
import {
  getEmpleados,
  createEmpleado,
  getEmpleado,
  getEmpleadoDetalle,
  updateEmpleado,
  deleteEmpleado,
  EmpleadosListParams,
  CreateEmpleadoBody,
  UpdateEmpleadoBody,
  ERROR_TIENE_TAREAS,
} from './empleado.service';

const mockToken = 'test-token';

vi.mock('../../../shared/utils/tokenStorage', () => ({
  getToken: vi.fn(() => mockToken),
}));

describe('empleado.service — getEmpleados (TR-018)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  const params: EmpleadosListParams = {
    page: 1,
    page_size: 20,
    sort: 'nombre',
    sort_dir: 'asc',
  };

  it('retorna éxito cuando la API responde 200', async () => {
    const empleados = [
      {
        id: 1,
        code: 'JPEREZ',
        nombre: 'Juan Pérez',
        email: 'juan@ejemplo.com',
        supervisor: false,
        activo: true,
        inhabilitado: false,
        created_at: '2025-01-15T10:00:00Z',
        updated_at: '2025-01-15T10:00:00Z',
      },
    ];
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleados obtenidos correctamente',
          resultado: {
            items: empleados,
            page: 1,
            page_size: 20,
            total: 1,
            total_pages: 1,
          },
        }),
    });

    const result = await getEmpleados(params);

    expect(result.success).toBe(true);
    expect(result.data).toEqual(empleados);
    expect(result.pagination).toEqual({
      page: 1,
      page_size: 20,
      total: 1,
      total_pages: 1,
    });
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/empleados'),
      expect.any(Object)
    );
  });

  it('construye correctamente los query params', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleados obtenidos correctamente',
          resultado: { items: [], page: 1, page_size: 15, total: 0, total_pages: 0 },
        }),
    });

    await getEmpleados({
      page: 2,
      page_size: 15,
      search: 'test',
      supervisor: true,
      activo: true,
      inhabilitado: false,
      sort: 'code',
      sort_dir: 'desc',
    });

    const url = (fetch as ReturnType<typeof vi.fn>).mock.calls[0][0];
    expect(url).toContain('page=2');
    expect(url).toContain('page_size=15');
    expect(url).toContain('search=test');
    expect(url).toContain('supervisor=1');
    expect(url).toContain('activo=1');
    expect(url).toContain('inhabilitado=0');
    expect(url).toContain('sort=code');
    expect(url).toContain('sort_dir=desc');
  });

  it('retorna error 403 cuando no es supervisor', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 403,
      json: () =>
        Promise.resolve({
          error: 3101,
          respuesta: 'No tiene permiso para acceder a esta funcionalidad',
          resultado: null,
        }),
    });

    const result = await getEmpleados(params);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(3101);
    expect(result.errorMessage).toBe('No tiene permiso para acceder a esta funcionalidad');
  });

  it('retorna error 401 cuando no está autenticado', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 401,
      json: () =>
        Promise.resolve({
          error: 3001,
          respuesta: 'Usuario no autenticado',
          resultado: null,
        }),
    });

    const result = await getEmpleados(params);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(3001);
    expect(result.errorMessage).toBe('Usuario no autenticado');
  });

  it('retorna error cuando la API responde con error diferente de 0', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 500,
          respuesta: 'Error interno del servidor',
          resultado: null,
        }),
    });

    const result = await getEmpleados(params);

    expect(result.success).toBe(false);
    expect(result.errorMessage).toBe('Error interno del servidor');
  });

  it('maneja correctamente cuando no hay resultados', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleados obtenidos correctamente',
          resultado: {
            items: [],
            page: 1,
            page_size: 20,
            total: 0,
            total_pages: 0,
          },
        }),
    });

    const result = await getEmpleados(params);

    expect(result.success).toBe(true);
    expect(result.data).toEqual([]);
    expect(result.pagination?.total).toBe(0);
  });

  it('no incluye params opcionales vacíos en la URL', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleados obtenidos correctamente',
          resultado: { items: [], page: 1, page_size: 20, total: 0, total_pages: 0 },
        }),
    });

    await getEmpleados({
      page: 1,
      page_size: 20,
    });

    const url = (fetch as ReturnType<typeof vi.fn>).mock.calls[0][0];
    expect(url).not.toContain('search=');
    expect(url).not.toContain('supervisor=');
    expect(url).not.toContain('activo=');
    expect(url).not.toContain('inhabilitado=');
  });
});

describe('empleado.service — createEmpleado (TR-019)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  const body: CreateEmpleadoBody = {
    code: 'JPEREZ',
    nombre: 'Juan Pérez',
    email: 'juan@ejemplo.com',
    password: 'password123',
    supervisor: false,
    activo: true,
    inhabilitado: false,
  };

  it('retorna éxito cuando la API responde 201', async () => {
    const created = {
      id: 1,
      code: 'JPEREZ',
      nombre: 'Juan Pérez',
      email: 'juan@ejemplo.com',
      supervisor: false,
      activo: true,
      inhabilitado: false,
      created_at: '2026-02-05T10:00:00Z',
      updated_at: '2026-02-05T10:00:00Z',
    };
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 201,
      json: () => Promise.resolve({ error: 0, respuesta: 'Empleado creado correctamente', resultado: created }),
    });

    const result = await createEmpleado(body);

    expect(result.success).toBe(true);
    expect(result.data).toEqual(created);
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/empleados'),
      expect.objectContaining({
        method: 'POST',
        headers: expect.objectContaining({
          'Content-Type': 'application/json',
          Authorization: `Bearer ${mockToken}`,
        }),
        body: expect.any(String),
      })
    );
    const sentBody = JSON.parse((fetch as ReturnType<typeof vi.fn>).mock.calls[0][1].body);
    expect(sentBody.code).toBe('JPEREZ');
    expect(sentBody.nombre).toBe('Juan Pérez');
    expect(sentBody.password).toBe('password123');
    expect(sentBody.supervisor).toBe(false);
  });

  it('retorna error de validación cuando la API responde 422', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 422,
      json: () =>
        Promise.resolve({
          error: 422,
          respuesta: 'El código es obligatorio.',
          resultado: { errors: { code: ['El código es obligatorio.'] } },
        }),
    });

    const result = await createEmpleado(body);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(422);
    expect(result.errorMessage).toBeDefined();
    expect(result.validationErrors).toEqual({ code: ['El código es obligatorio.'] });
  });

  it('retorna conflicto cuando la API responde 409 (código duplicado)', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 409,
      json: () =>
        Promise.resolve({
          error: 4101,
          respuesta: 'El código del empleado ya existe',
          resultado: null,
        }),
    });

    const result = await createEmpleado(body);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(4101);
    expect(result.errorMessage).toBeDefined();
  });

  it('retorna error 403 cuando no es supervisor', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 403,
      json: () =>
        Promise.resolve({
          error: 3101,
          respuesta: 'No tiene permiso para acceder a esta funcionalidad',
          resultado: null,
        }),
    });

    const result = await createEmpleado(body);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(3101);
  });

  it('envía valores por defecto correctos (supervisor false, activo true)', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 201,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleado creado correctamente',
          resultado: {
            id: 1,
            code: 'TEST',
            nombre: 'Test',
            email: null,
            supervisor: false,
            activo: true,
            inhabilitado: false,
            created_at: '2026-02-05T10:00:00Z',
            updated_at: '2026-02-05T10:00:00Z',
          },
        }),
    });

    await createEmpleado({
      code: 'TEST',
      nombre: 'Test',
      password: 'password123',
    });

    const sentBody = JSON.parse((fetch as ReturnType<typeof vi.fn>).mock.calls[0][1].body);
    expect(sentBody.supervisor).toBe(false);
    expect(sentBody.activo).toBe(true);
    expect(sentBody.inhabilitado).toBe(false);
  });
});

describe('empleado.service — getEmpleado (TR-020)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('retorna éxito cuando la API responde 200', async () => {
    const empleado = {
      id: 1,
      code: 'JPEREZ',
      nombre: 'Juan Pérez',
      email: 'juan@ejemplo.com',
      supervisor: false,
      activo: true,
      inhabilitado: false,
      created_at: '2025-01-15T10:00:00Z',
      updated_at: '2025-01-15T10:00:00Z',
    };
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleado obtenido correctamente',
          resultado: empleado,
        }),
    });

    const result = await getEmpleado(1);

    expect(result.success).toBe(true);
    expect(result.data).toEqual(empleado);
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/empleados/1'),
      expect.any(Object)
    );
  });

  it('retorna error 404 cuando el empleado no existe', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 404,
      json: () =>
        Promise.resolve({
          error: 4001,
          respuesta: 'Empleado no encontrado',
          resultado: null,
        }),
    });

    const result = await getEmpleado(999);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(4001);
    expect(result.errorMessage).toBe('Empleado no encontrado');
  });

  it('retorna error 403 cuando no es supervisor', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 403,
      json: () =>
        Promise.resolve({
          error: 3101,
          respuesta: 'No tiene permiso para acceder a esta funcionalidad',
          resultado: null,
        }),
    });

    const result = await getEmpleado(1);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(3101);
    expect(result.errorMessage).toBe('No tiene permiso para acceder a esta funcionalidad');
  });

  it('retorna error cuando la API responde con error diferente de 0', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 500,
          respuesta: 'Error interno del servidor',
          resultado: null,
        }),
    });

    const result = await getEmpleado(1);

    expect(result.success).toBe(false);
    expect(result.errorMessage).toBe('Error interno del servidor');
  });
});

describe('empleado.service — getEmpleadoDetalle (TR-022)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('retorna éxito con datos y total_tareas cuando la API responde 200', async () => {
    const empleado = {
      id: 1,
      code: 'JPEREZ',
      nombre: 'Juan Pérez',
      email: 'juan@ejemplo.com',
      supervisor: false,
      activo: true,
      inhabilitado: false,
      created_at: '2025-01-15T10:00:00Z',
      updated_at: '2025-01-20T11:00:00Z',
      total_tareas: 42,
    };
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleado obtenido correctamente',
          resultado: empleado,
        }),
    });

    const result = await getEmpleadoDetalle(1);

    expect(result.success).toBe(true);
    expect(result.data).toEqual(empleado);
    expect(result.data?.total_tareas).toBe(42);
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/empleados/1?include_stats=true'),
      expect.any(Object)
    );
  });

  it('retorna error 404 cuando el empleado no existe', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 404,
      json: () =>
        Promise.resolve({
          error: 4003,
          respuesta: 'Empleado no encontrado',
          resultado: null,
        }),
    });

    const result = await getEmpleadoDetalle(999);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(4003);
    expect(result.errorMessage).toBe('Empleado no encontrado');
  });

  it('retorna error 403 cuando no es supervisor', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 403,
      json: () =>
        Promise.resolve({
          error: 3101,
          respuesta: 'No tiene permiso para acceder a esta funcionalidad',
          resultado: null,
        }),
    });

    const result = await getEmpleadoDetalle(1);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(3101);
  });
});

describe('empleado.service — updateEmpleado (TR-020)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  const body: UpdateEmpleadoBody = {
    nombre: 'Juan Pérez Actualizado',
    email: 'juan.nuevo@ejemplo.com',
    supervisor: true,
    activo: true,
    inhabilitado: false,
  };

  it('retorna éxito cuando la API responde 200', async () => {
    const updated = {
      id: 1,
      code: 'JPEREZ',
      nombre: 'Juan Pérez Actualizado',
      email: 'juan.nuevo@ejemplo.com',
      supervisor: true,
      activo: true,
      inhabilitado: false,
      updated_at: '2026-02-05T12:00:00Z',
    };
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleado actualizado correctamente',
          resultado: updated,
        }),
    });

    const result = await updateEmpleado(1, body);

    expect(result.success).toBe(true);
    expect(result.data).toEqual(updated);
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/empleados/1'),
      expect.objectContaining({
        method: 'PUT',
        headers: expect.objectContaining({
          'Content-Type': 'application/json',
          Authorization: `Bearer ${mockToken}`,
        }),
        body: expect.any(String),
      })
    );
    const sentBody = JSON.parse((fetch as ReturnType<typeof vi.fn>).mock.calls[0][1].body);
    expect(sentBody.nombre).toBe('Juan Pérez Actualizado');
    expect(sentBody.email).toBe('juan.nuevo@ejemplo.com');
    expect(sentBody.supervisor).toBe(true);
    expect(sentBody.password).toBeUndefined();
  });

  it('envía password solo si está presente', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleado actualizado correctamente',
          resultado: {
            id: 1,
            code: 'JPEREZ',
            nombre: 'Juan Pérez',
            email: 'juan@ejemplo.com',
            supervisor: false,
            activo: true,
            inhabilitado: false,
            updated_at: '2026-02-05T12:00:00Z',
          },
        }),
    });

    await updateEmpleado(1, {
      nombre: 'Juan Pérez',
      password: 'nuevaPassword123',
    });

    const sentBody = JSON.parse((fetch as ReturnType<typeof vi.fn>).mock.calls[0][1].body);
    expect(sentBody.password).toBe('nuevaPassword123');
  });

  it('no envía password si no está presente', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleado actualizado correctamente',
          resultado: {
            id: 1,
            code: 'JPEREZ',
            nombre: 'Juan Pérez',
            email: 'juan@ejemplo.com',
            supervisor: false,
            activo: true,
            inhabilitado: false,
            updated_at: '2026-02-05T12:00:00Z',
          },
        }),
    });

    await updateEmpleado(1, {
      nombre: 'Juan Pérez',
    });

    const sentBody = JSON.parse((fetch as ReturnType<typeof vi.fn>).mock.calls[0][1].body);
    expect(sentBody.password).toBeUndefined();
  });

  it('retorna error de validación cuando la API responde 422', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 422,
      json: () =>
        Promise.resolve({
          error: 422,
          respuesta: 'El nombre es obligatorio.',
          resultado: { errors: { nombre: ['El nombre es obligatorio.'] } },
        }),
    });

    const result = await updateEmpleado(1, body);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(422);
    expect(result.errorMessage).toBeDefined();
    expect(result.validationErrors).toEqual({ nombre: ['El nombre es obligatorio.'] });
  });

  it('retorna conflicto cuando la API responde 409 (email duplicado)', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 409,
      json: () =>
        Promise.resolve({
          error: 4102,
          respuesta: 'El email ya está registrado',
          resultado: null,
        }),
    });

    const result = await updateEmpleado(1, body);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(4102);
    expect(result.errorMessage).toBe('El email ya está registrado');
  });

  it('retorna error 404 cuando el empleado no existe', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 404,
      json: () =>
        Promise.resolve({
          error: 4001,
          respuesta: 'Empleado no encontrado',
          resultado: null,
        }),
    });

    const result = await updateEmpleado(999, body);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(4001);
    expect(result.errorMessage).toBe('Empleado no encontrado');
  });

  it('retorna error 403 cuando no es supervisor', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 403,
      json: () =>
        Promise.resolve({
          error: 3101,
          respuesta: 'No tiene permiso para editar empleados',
          resultado: null,
        }),
    });

    const result = await updateEmpleado(1, body);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(3101);
    expect(result.errorMessage).toBe('No tiene permiso para editar empleados');
  });

  it('maneja correctamente email null', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleado actualizado correctamente',
          resultado: {
            id: 1,
            code: 'JPEREZ',
            nombre: 'Juan Pérez',
            email: null,
            supervisor: false,
            activo: true,
            inhabilitado: false,
            updated_at: '2026-02-05T12:00:00Z',
          },
        }),
    });

    await updateEmpleado(1, {
      nombre: 'Juan Pérez',
      email: null,
    });

    const sentBody = JSON.parse((fetch as ReturnType<typeof vi.fn>).mock.calls[0][1].body);
    expect(sentBody.email).toBeNull();
  });
});

describe('empleado.service — deleteEmpleado (TR-021)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('retorna éxito cuando la API responde 200', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Empleado eliminado correctamente',
          resultado: null,
        }),
    });

    const result = await deleteEmpleado(1);

    expect(result.success).toBe(true);
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/empleados/1'),
      expect.objectContaining({
        method: 'DELETE',
        headers: expect.objectContaining({
          Authorization: `Bearer ${mockToken}`,
        }),
      })
    );
  });

  it('retorna error 422 cuando el empleado tiene tareas asociadas', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 422,
      json: () =>
        Promise.resolve({
          error: ERROR_TIENE_TAREAS,
          respuesta: 'No se puede eliminar un empleado que tiene tareas asociadas.',
          resultado: null,
        }),
    });

    const result = await deleteEmpleado(1);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(ERROR_TIENE_TAREAS);
    expect(result.errorMessage).toBe('No se puede eliminar un empleado que tiene tareas asociadas.');
  });

  it('retorna error 404 cuando el empleado no existe', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 404,
      json: () =>
        Promise.resolve({
          error: 4003,
          respuesta: 'Empleado no encontrado',
          resultado: null,
        }),
    });

    const result = await deleteEmpleado(999);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(4003);
    expect(result.errorMessage).toBe('Empleado no encontrado');
  });

  it('retorna error 403 cuando no es supervisor', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 403,
      json: () =>
        Promise.resolve({
          error: 3101,
          respuesta: 'No tiene permiso para eliminar empleados',
          resultado: null,
        }),
    });

    const result = await deleteEmpleado(1);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(3101);
    expect(result.errorMessage).toBe('No tiene permiso para eliminar empleados');
  });
});
