import { test } from '@hyvor/laravel-playwright';
import { expect } from '@playwright/test';
import { DashboardPage } from '../pages/DashboardPage';
import { CustodianshipFormPage } from '../pages/CustodianshipFormPage';
import { CustodianshipShowPage } from '../pages/CustodianshipShowPage';
import { loginAsUser } from '../helpers/auth.helpers';
import { resetDatabase, createCustodianship } from '../helpers/database.helpers';
import { validCustodianship } from '../fixtures/custodianship.fixtures';

test.describe.configure({ mode: 'serial' });

test.describe('Timer Reset', () => {
  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);
  });

  test('user can reset timer from show page', async ({ page, laravel }) => {
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Timer Reset Test');
    await formPage.fillMessage('Testing timer reset');
    await formPage.addRecipient('recipient@example.com');

    await formPage.clickSave();
    await showPage.clickActivate();
    await page.waitForTimeout(500);

    await showPage.clickResetTimer();

    await page.waitForTimeout(500);

    await showPage.expandResetHistory();
    const rows = await page.locator('table tbody tr').count();
    expect(rows).toBeGreaterThan(0);
  });

  test('user can reset timer from dashboard', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    const custodianshipName = 'Dashboard Reset Test';

    await formPage.fillName(custodianshipName);
    await formPage.fillMessage('Testing dashboard reset');
    await formPage.addRecipient('recipient@example.com');

    await formPage.clickSave();

    const showPage = new CustodianshipShowPage(page);
    await showPage.clickActivate();
    await page.waitForTimeout(500);

    await dashboard.goto();

    await dashboard.resetTimerForCustodianship(custodianshipName);

    await dashboard.verifyCustodianshipExists(custodianshipName);
  });

  test('cannot reset timer for draft custodianship', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Draft Custodianship');
    await formPage.fillMessage('Still in draft');
    await formPage.addRecipient('recipient@example.com');

    await formPage.clickSave();

    await showPage.verifyStatus('draft');

    const resetButton = page.locator('[data-testid="reset-timer-button"]');
    await expect(resetButton).not.toBeVisible();
  });
});
