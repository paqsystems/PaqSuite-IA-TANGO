/**
 * Tests unitarios: auth.service — forgotPassword, resetPassword (TR-004)
 *
 * @see TR-004(SH)-recuperación-de-contraseña.md
 */

import { describe, it, expect, vi, beforeEach } from 'vitest';
import { forgotPassword, resetPassword } from './auth.service';

describe('auth.service — forgotPassword (TR-004)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('retorna success cuando la API responde 200 con error 0', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Si el usuario existe y tiene email configurado, recibirá un enlace para restablecer la contraseña.',
          resultado: {},
        }),
    });

    const result = await forgotPassword('JPEREZ');

    expect(result.success).toBe(true);
    expect(result.message).toContain('recibirá un enlace');
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/auth/forgot-password'),
      expect.objectContaining({
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ code_or_email: 'JPEREZ' }),
      })
    );
  });

  it('envía code_or_email con trim', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({ error: 0, respuesta: 'Ok', resultado: {} }),
    });

    await forgotPassword('  user@mail.com  ');

    const body = JSON.parse((fetch as ReturnType<typeof vi.fn>).mock.calls[0][1].body);
    expect(body.code_or_email).toBe('user@mail.com');
  });

  it('retorna success false cuando la API responde con error distinto de 0', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({ error: 1000, respuesta: 'Error de validación', resultado: {} }),
    });

    const result = await forgotPassword('x');

    expect(result.success).toBe(false);
    expect(result.errorMessage).toBeDefined();
  });

  it('retorna success false en error de red', async () => {
    globalThis.fetch = vi.fn().mockRejectedValue(new Error('Network error'));

    const result = await forgotPassword('JPEREZ');

    expect(result.success).toBe(false);
    expect(result.errorMessage).toContain('conexión');
  });
});

describe('auth.service — resetPassword (TR-004)', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('retorna success cuando la API responde 200 con error 0', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      ok: true,
      json: () =>
        Promise.resolve({
          error: 0,
          respuesta: 'Contraseña restablecida correctamente.',
          resultado: {},
        }),
    });

    const result = await resetPassword('token-abc', 'newPass123', 'newPass123');

    expect(result.success).toBe(true);
    expect(fetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/v1/auth/reset-password'),
      expect.objectContaining({
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          token: 'token-abc',
          password: 'newPass123',
          password_confirmation: 'newPass123',
        }),
      })
    );
  });

  it('retorna success false cuando la API responde 422 (token inválido)', async () => {
    globalThis.fetch = vi.fn().mockResolvedValue({
      ok: false,
      json: () =>
        Promise.resolve({
          error: 3205,
          respuesta: 'El enlace de recuperación no es válido o ha expirado.',
          resultado: {},
        }),
    });

    const result = await resetPassword('bad-token', 'newPass123', 'newPass123');

    expect(result.success).toBe(false);
    expect(result.errorMessage).toBeDefined();
  });

  it('retorna success false en error de red', async () => {
    globalThis.fetch = vi.fn().mockRejectedValue(new Error('Network error'));

    const result = await resetPassword('t', 'p', 'p');

    expect(result.success).toBe(false);
    expect(result.errorMessage).toContain('conexión');
  });
});
