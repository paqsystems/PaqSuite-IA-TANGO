/**
 * Test E2E: Grupos empresarios (TR-001)
 *
 * Listar grupos, acceder a crear (formulario vacío o redirección).
 * Requiere backend en ejecución y usuario admin (EMP / emple123).
 *
 * @see docs/04-tareas/002-GruposEmpresarios/TR-001-listado-grupos-empresarios.md
 */

import { test, expect } from '@playwright/test';

test.describe('Grupos empresarios', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.getByTestId('login.codigo').fill('EMP');
    await page.getByTestId('login.password').fill('emple123');
    await page.getByTestId('login.submit').click();
    await expect(page).toHaveURL(/\/(\?.*)?$|(\/select-empresa)?/);
    if (page.url().includes('select-empresa')) {
      const firstEmpresa = page.locator('[data-testid^="empresaSelector.option."]').first();
      await firstEmpresa.click({ timeout: 5000 });
      await expect(page).toHaveURL(/\/(\?.*)?$/, { timeout: 5000 });
    }
  });

  test('listar grupos empresarios muestra grilla', async ({ page }) => {
    await page.goto('/admin/grupos-empresarios');
    await expect(page.getByTestId('grupos-empresarios.admin')).toBeVisible({ timeout: 10000 });
    await expect(page.getByTestId('grupos-empresarios.grid')).toBeVisible();
  });

  test('acceder a crear desde listado', async ({ page }) => {
    await page.goto('/admin/grupos-empresarios');
    await page.getByTestId('grupos-empresarios.create').click();
    await expect(page).toHaveURL(/\/admin\/grupos-empresarios\/crear/);
    await expect(page.getByTestId('grupos-empresarios.crear')).toBeVisible();
  });

  test('crear grupo con empresas', async ({ page }) => {
    await page.goto('/admin/grupos-empresarios/crear');
    await expect(page.getByTestId('grupoEmpresario.create.form')).toBeVisible({ timeout: 10000 });

    await page.getByTestId('grupoEmpresario.create.descripcion').fill('Grupo E2E Test');
    const empresasContainer = page.getByTestId('grupoEmpresario.create.empresas');
    await empresasContainer.locator('.dx-dropdowneditor-icon').click();
    await page.locator('.dx-list-item').first().click({ timeout: 5000 });
    await page.getByTestId('grupoEmpresario.create.submit').click();

    await expect(page).toHaveURL(/\/admin\/grupos-empresarios$/);
    await expect(page.getByTestId('grupos-empresarios.admin')).toBeVisible();
  });
});
