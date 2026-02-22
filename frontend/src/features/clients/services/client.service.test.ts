/**
 * Tests unitarios: client.service (createCliente, getCliente, updateCliente, deleteCliente, tipos-tarea) — TR-009, TR-010, TR-011, TR-012
 *
 * @see TR-009(MH)-creación-de-cliente.md
 * @see TR-010(MH)-edición-de-cliente.md
 * @see TR-011(MH)-eliminación-de-cliente.md
 * @see TR-012(MH)-asignación-de-tipos-de-tarea-a-cliente.md
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { getToken } from '../../../shared/utils/tokenStorage';
import {
  createCliente,
  CreateClienteBody,
  getCliente,
  updateCliente,
  UpdateClienteBody,
  deleteCliente,
  getTiposTareaCliente,
  updateTiposTareaCliente,
  getTiposTareaParaAsignacion,
  ERROR_TIENE_TAREAS,
  ERROR_SIN_TIPOS_TAREA,
} from './client.service';

const mockToken = 'test-token';

vi.mock('../../../shared/utils/tokenStorage', () => ({
  getToken: vi.fn(() => mockToken),
}));

describe('client.service — createCliente (TR-009)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  const body: CreateClienteBody = {
    code: 'CLI001',
    nombre: 'Cliente A',
    tipo_cliente_id: 1,
    email: null,
    habilitar_acceso: false,
    activo: true,
    inhabilitado: false,
  };

  it('retorna éxito cuando la API responde 201', async () => {
    const created = {
      id: 1,
      code: 'CLI001',
      nombre: 'Cliente A',
      tipo_cliente_id: 1,
      tipo_cliente: { id: 1, code: 'CORP', descripcion: 'Corporativo' },
      email: null,
      activo: true,
      inhabilitado: false,
      created_at: '2026-01-31T10:00:00Z',
      updated_at: '2026-01-31T10:00:00Z',
    };
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 201,
      json: () => Promise.resolve({ error: 0, respuesta: 'Cliente creado correctamente', resultado: created }),
    });

    const result = await createCliente(body);

    expect(result.success).toBe(true);
    expect(result.data).toEqual(created);
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/clientes'),
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
    expect(sentBody.code).toBe('CLI001');
    expect(sentBody.nombre).toBe('Cliente A');
    expect(sentBody.tipo_cliente_id).toBe(1);
    expect(sentBody.habilitar_acceso).toBe(false);
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

    const result = await createCliente(body);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(422);
    expect(result.errorMessage).toBeDefined();
    expect(result.validationErrors).toEqual({ nombre: ['El nombre es obligatorio.'] });
  });

  it('retorna conflicto cuando la API responde 409 (código duplicado)', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 409,
      json: () =>
        Promise.resolve({
          error: 4101,
          respuesta: 'El código del cliente ya existe.',
          resultado: null,
        }),
    });

    const result = await createCliente(body);

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

    const result = await createCliente(body);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(3101);
  });
});

describe('client.service — getCliente / updateCliente (TR-010)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getCliente retorna éxito cuando la API responde 200', async () => {
    const detalle = {
      id: 1,
      code: 'CLI001',
      nombre: 'Cliente A',
      tipo_cliente_id: 1,
      tipo_cliente: { id: 1, code: 'CORP', descripcion: 'Corporativo' },
      email: null,
      activo: true,
      inhabilitado: false,
      tiene_acceso: false,
      created_at: '2026-01-31T10:00:00Z',
      updated_at: '2026-01-31T10:00:00Z',
    };
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () => Promise.resolve({ error: 0, respuesta: 'Cliente obtenido correctamente', resultado: detalle }),
    });

    const result = await getCliente(1);

    expect(result.success).toBe(true);
    expect(result.data).toEqual(detalle);
    expect(result.data?.tiene_acceso).toBe(false);
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/clientes/1'),
      expect.any(Object)
    );
  });

  it('getCliente retorna error cuando la API responde 404', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 404,
      json: () =>
        Promise.resolve({
          error: 4003,
          respuesta: 'Cliente no encontrado',
          resultado: null,
        }),
    });

    const result = await getCliente(999);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(4003);
  });

  it('updateCliente retorna éxito cuando la API responde 200', async () => {
    const actualizado = {
      id: 1,
      code: 'CLI001',
      nombre: 'Cliente A Actualizado',
      tipo_cliente_id: 1,
      tipo_cliente: { id: 1, code: 'CORP', descripcion: 'Corporativo' },
      email: null,
      activo: true,
      inhabilitado: false,
      updated_at: '2026-01-31T12:00:00Z',
    };
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Cliente actualizado correctamente',
          resultado: actualizado,
        }),
    });

    const body: UpdateClienteBody = {
      nombre: 'Cliente A Actualizado',
      tipo_cliente_id: 1,
      activo: true,
      inhabilitado: false,
    };
    const result = await updateCliente(1, body);

    expect(result.success).toBe(true);
    expect(result.data?.nombre).toBe('Cliente A Actualizado');
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/clientes/1'),
      expect.objectContaining({
        method: 'PUT',
        headers: expect.objectContaining({ 'Content-Type': 'application/json' }),
      })
    );
  });

  it('updateCliente retorna 422 cuando la API responde validación', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 422,
      json: () =>
        Promise.resolve({
          error: 422,
          respuesta: 'El nombre es obligatorio.',
          resultado: { errors: { nombre: ['El nombre es obligatorio.'] } },
        }),
    });

    const result = await updateCliente(1, {
      nombre: '',
      tipo_cliente_id: 1,
    });

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(422);
    expect(result.validationErrors).toEqual({ nombre: ['El nombre es obligatorio.'] });
  });
});

describe('client.service — deleteCliente (TR-011)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('retorna éxito cuando la API responde 200', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () => Promise.resolve({ error: 0, respuesta: 'Cliente eliminado correctamente', resultado: null }),
    });

    const result = await deleteCliente(1);

    expect(result.success).toBe(true);
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/clientes/1'),
      expect.objectContaining({ method: 'DELETE' })
    );
  });

  it('retorna error 2112 cuando el cliente tiene tareas asociadas', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 422,
      json: () =>
        Promise.resolve({
          error: ERROR_TIENE_TAREAS,
          respuesta: 'No se puede eliminar un cliente que tiene tareas asociadas.',
          resultado: null,
        }),
    });

    const result = await deleteCliente(1);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(ERROR_TIENE_TAREAS);
    expect(result.errorMessage).toContain('tareas asociadas');
  });

  it('retorna error cuando la API responde 404', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 404,
      json: () =>
        Promise.resolve({
          error: 4003,
          respuesta: 'Cliente no encontrado',
          resultado: null,
        }),
    });

    const result = await deleteCliente(999);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(4003);
  });
});

describe('client.service — getTiposTareaCliente / updateTiposTareaCliente (TR-012)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('getTiposTareaCliente retorna éxito cuando la API responde 200', async () => {
    const tipos = [
      { id: 2, code: 'TIPO002', descripcion: 'Tipo Específico', is_generico: false, activo: true, inhabilitado: false },
    ];
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Tipos de tarea obtenidos correctamente',
          resultado: tipos,
        }),
    });

    const result = await getTiposTareaCliente(1);

    expect(result.success).toBe(true);
    expect(result.data).toEqual(tipos);
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/clientes/1/tipos-tarea'),
      expect.any(Object)
    );
  });

  it('getTiposTareaCliente retorna error cuando la API responde 404', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 404,
      json: () =>
        Promise.resolve({ error: 4003, respuesta: 'Cliente no encontrado', resultado: null }),
    });

    const result = await getTiposTareaCliente(999);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(4003);
  });

  it('updateTiposTareaCliente retorna éxito cuando la API responde 200', async () => {
    const tipos = [
      { id: 2, code: 'TIPO002', descripcion: 'Tipo Específico', is_generico: false, activo: true, inhabilitado: false },
    ];
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Tipos de tarea actualizados correctamente',
          resultado: tipos,
        }),
    });

    const result = await updateTiposTareaCliente(1, [2]);

    expect(result.success).toBe(true);
    expect(result.data).toEqual(tipos);
    const call = (fetch as ReturnType<typeof vi.fn>).mock.calls[0];
    expect(call[0]).toContain('/api/v1/clientes/1/tipos-tarea');
    expect(call[1].method).toBe('PUT');
    const body = JSON.parse(call[1].body);
    expect(body.tipo_tarea_ids).toEqual([2]);
  });

  it('updateTiposTareaCliente retorna error 2116 cuando lista vacía y no hay genéricos', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 422,
      json: () =>
        Promise.resolve({
          error: ERROR_SIN_TIPOS_TAREA,
          respuesta: 'El cliente debe tener al menos un tipo de tarea disponible (genérico o asignado).',
          resultado: null,
        }),
    });

    const result = await updateTiposTareaCliente(1, []);

    expect(result.success).toBe(false);
    expect(result.errorCode).toBe(ERROR_SIN_TIPOS_TAREA);
  });

  it('getTiposTareaParaAsignacion retorna solo tipos no genéricos', async () => {
    const todos = [
      { id: 1, code: 'GEN', descripcion: 'Genérico', is_generico: true },
      { id: 2, code: 'ESP', descripcion: 'Específico', is_generico: false },
    ];
    globalThis.fetch = vi.fn().mockResolvedValue({
      status: 200,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Tipos de tarea obtenidos correctamente',
          resultado: todos,
        }),
    });

    const result = await getTiposTareaParaAsignacion();

    expect(result.success).toBe(true);
    expect(result.data).toHaveLength(1);
    expect(result.data![0].id).toBe(2);
    expect(result.data![0].is_generico).toBe(false);
  });
});
