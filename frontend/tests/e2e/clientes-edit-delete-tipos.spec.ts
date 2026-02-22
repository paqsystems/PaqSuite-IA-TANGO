/**
 * Tests E2E: Edición, eliminación y tipos de tarea de cliente — TR-010, TR-011, TR-012
 *
 * TR-010: Supervisor edita cliente (nombre, etc.) y guarda.
 * TR-011: Supervisor elimina cliente (modal confirmación).
 * TR-012: Supervisor asigna/desasigna tipos de tarea en edición de cliente.
 *
 * @see docs/hu-tareas/TR-010(MH)-edición-de-cliente.md
 * @see docs/hu-tareas/TR-011(MH)-eliminación-de-cliente.md
 * @see docs/hu-tareas/TR-012(MH)-asignación-de-tipos-de-tarea-a-cliente.md
 */

import { test, expect } from '@playwright/test';

const SUPERVISOR = {
  code: 'MGARCIA',
  password: 'password456',
};

async function loginAsSupervisor(page: import('@playwright/test').Page) {
  await page.goto('/login');
  await page.evaluate(() => localStorage.clear());
  await expect(page.locator('[data-testid="auth.login.form"]')).toBeVisible({ timeout: 15000 });
  await page.fill('[data-testid="auth.login.usuarioInput"]', SUPERVISOR.code);
  await page.fill('[data-testid="auth.login.passwordInput"]', SUPERVISOR.password);
  await Promise.all([
    page.waitForResponse((resp) => resp.url().includes('/api/v1/auth/login') && resp.status() === 200),
    page.click('[data-testid="auth.login.submitButton"]'),
  ]);
  await expect(page).toHaveURL('/', { timeout: 20000 });
}

test.describe('Edición de Cliente — TR-010', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await loginAsSupervisor(page);
  });

  test('supervisor puede abrir edición desde listado y ver formulario', async ({ page }) => {
    await page.goto('/clientes');
    await expect(page.locator('[data-testid="clientes.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="clientes.loading"]')).not.toBeVisible({ timeout: 15000 });

    const editButton = page.locator('[data-testid^="clientes.edit."]').first();
    const count = await editButton.count();
    if (count === 0) {
      test.skip();
      return;
    }
    await editButton.click();
    await expect(page).toHaveURL(/\/clientes\/\d+\/editar/, { timeout: 10000 });
    await expect(page.locator('[data-testid="clientes.edit.page"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="clientes.edit.form"]')).toBeVisible();
    await expect(page.locator('[data-testid="clientes.edit.nombre"]')).toBeVisible();
    await expect(page.locator('[data-testid="clientes.edit.submit"]')).toBeVisible();
  });

  test('supervisor edita nombre y guarda', async ({ page }) => {
    await page.goto('/clientes');
    await expect(page.locator('[data-testid="clientes.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="clientes.loading"]')).not.toBeVisible({ timeout: 15000 });

    const editButton = page.locator('[data-testid^="clientes.edit."]').first();
    if ((await editButton.count()) === 0) {
      test.skip();
      return;
    }
    await editButton.click();
    await expect(page).toHaveURL(/\/clientes\/\d+\/editar/, { timeout: 10000 });
    await expect(page.locator('[data-testid="clientes.edit.form"]')).toBeVisible({ timeout: 15000 });

    const nombreInput = page.locator('[data-testid="clientes.edit.nombre"]');
    await nombreInput.fill('');
    const nuevoNombre = 'Cliente E2E TR-010 ' + Date.now();
    await nombreInput.fill(nuevoNombre);

    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/clientes/') && resp.request().method() === 'PUT', { timeout: 15000 }),
      page.click('[data-testid="clientes.edit.submit"]'),
    ]);

    await expect(
      page.locator('[data-testid="clientes.edit.success"]').or(page.locator('[data-testid="clientes.list"]'))
    ).toBeVisible({ timeout: 15000 });
  });
});

test.describe('Eliminación de Cliente — TR-011', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await loginAsSupervisor(page);
  });

  test('supervisor ve modal de confirmación al pulsar eliminar', async ({ page }) => {
    await page.goto('/clientes');
    await expect(page.locator('[data-testid="clientes.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="clientes.loading"]')).not.toBeVisible({ timeout: 15000 });

    const deleteBtn = page.locator('[data-testid$=".delete"]').first();
    if ((await deleteBtn.count()) === 0) {
      test.skip();
      return;
    }
    await deleteBtn.click();
    await expect(page.locator('[data-testid="clientes.delete.modal"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="clientes.delete.cancel"]')).toBeVisible();
    await expect(page.locator('[data-testid="clientes.delete.confirm"]')).toBeVisible();
    await page.click('[data-testid="clientes.delete.cancel"]');
    await expect(page.locator('[data-testid="clientes.delete.modal"]')).not.toBeVisible();
  });

  test('supervisor elimina cliente creado en test (crear y eliminar)', async ({ page }) => {
    await page.goto('/clientes/nueva');
    await expect(page.locator('[data-testid="clientes.create.form"]')).toBeVisible({ timeout: 10000 });
    const uniqueCode = 'E2E_DEL_' + Date.now();
    await page.fill('[data-testid="clientes.create.code"]', uniqueCode);
    await page.fill('[data-testid="clientes.create.nombre"]', 'Cliente a eliminar E2E');
    await page.selectOption('[data-testid="clientes.create.tipoCliente"]', { index: 1 });
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/clientes') && resp.request().method() === 'POST', { timeout: 15000 }),
      page.click('[data-testid="clientes.create.submit"]'),
    ]);
    await page.waitForURL(/\/(clientes|clientes\/nueva)$/, { timeout: 20000 });
    if (page.url().endsWith('/nueva')) {
      test.skip(true, 'Creación falló (p. ej. 2116 sin tipos genéricos en BD)');
      return;
    }

    const rowWithCode = page.getByRole('row').filter({ hasText: uniqueCode });
    await expect(rowWithCode).toBeVisible({ timeout: 15000 });
    await rowWithCode.locator('[data-testid$=".delete"]').click();
    await expect(page.locator('[data-testid="clientes.delete.modal"]')).toBeVisible({ timeout: 5000 });
    await expect(page.locator('[data-testid="clientes.delete.code"]')).toHaveText(uniqueCode);
    await page.click('[data-testid="clientes.delete.confirm"]');
    await expect(page.locator('[data-testid="clientes.delete.modal"]')).not.toBeVisible({ timeout: 10000 });
    await expect(page.locator('[data-testid="clientes.success"]').or(page.locator('[data-testid="clientes.list"]'))).toBeVisible({ timeout: 10000 });
  });
});

test.describe('Asignación de Tipos de Tarea a Cliente — TR-012', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(60000);
    await loginAsSupervisor(page);
  });

  test('supervisor ve sección Tipos de tarea en edición', async ({ page }) => {
    await page.goto('/clientes');
    await expect(page.locator('[data-testid="clientes.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="clientes.loading"]')).not.toBeVisible({ timeout: 15000 });

    const editButton = page.locator('[data-testid^="clientes.edit."]').first();
    if ((await editButton.count()) === 0) {
      test.skip();
      return;
    }
    await editButton.click();
    await expect(page).toHaveURL(/\/clientes\/\d+\/editar/, { timeout: 10000 });
    await expect(page.locator('[data-testid="clientes.edit.form"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="clientes.taskTypes.section"]')).toBeVisible({ timeout: 15000 });
  });

  test('supervisor asigna/desasigna tipo y guarda tipos de tarea', async ({ page }) => {
    await page.goto('/clientes');
    await expect(page.locator('[data-testid="clientes.list"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="clientes.loading"]')).not.toBeVisible({ timeout: 15000 });

    const editButton = page.locator('[data-testid^="clientes.edit."]').first();
    if ((await editButton.count()) === 0) {
      test.skip();
      return;
    }
    await editButton.click();
    await expect(page).toHaveURL(/\/clientes\/\d+\/editar/, { timeout: 10000 });
    await expect(page.locator('[data-testid="clientes.edit.form"]')).toBeVisible({ timeout: 15000 });
    await expect(page.locator('[data-testid="clientes.taskTypes.section"]')).toBeVisible({ timeout: 15000 });

    const list = page.locator('[data-testid="clientes.taskTypes.list"]');
    await expect(list).toBeVisible({ timeout: 15000 });
    const firstCheck = page.locator('[data-testid^="clientes.taskTypes.check."]').first();
    if ((await firstCheck.count()) === 0) {
      await expect(page.locator('[data-testid="clientes.taskTypes.save"]')).toBeVisible();
      test.skip();
      return;
    }
    await firstCheck.click();
    await Promise.all([
      page.waitForResponse((resp) => resp.url().includes('/api/v1/clientes/') && resp.url().includes('/tipos-tarea') && resp.request().method() === 'PUT', { timeout: 15000 }),
      page.click('[data-testid="clientes.taskTypes.save"]'),
    ]);
    await expect(page.locator('[data-testid="clientes.taskTypes.success"]')).toBeVisible({ timeout: 10000 });
  });
});
