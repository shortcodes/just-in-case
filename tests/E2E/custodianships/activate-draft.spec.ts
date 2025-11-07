import { test } from '@hyvor/laravel-playwright';
import { expect } from '@playwright/test';
import { DashboardPage } from '../pages/DashboardPage';
import { CustodianshipFormPage } from '../pages/CustodianshipFormPage';
import { CustodianshipShowPage } from '../pages/CustodianshipShowPage';
import { loginAsUser } from '../helpers/auth.helpers';
import { resetDatabase } from '../helpers/database.helpers';

test.describe.configure({ mode: 'serial' });

test.describe('Activate Draft Custodianship', () => {
  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);
  });

  test('user can activate draft custodianship from show page', async ({ page, laravel }) => {
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Draft to Activate');
    await formPage.fillMessage('This starts as draft');
    await formPage.addRecipient('recipient@example.com');

    await formPage.clickSave();

    await showPage.verifyStatus('draft');

    await showPage.clickActivate();

    await page.waitForTimeout(1000);

    await showPage.verifyStatus('active');
    await showPage.verifyTimerSection();
  });

  test('activated custodianship shows timer and can be reset', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Activate and Reset');
    await formPage.fillMessage('Testing activation and reset');
    await formPage.addRecipient('recipient@example.com');

    await formPage.clickSave();
    await showPage.clickActivate();
    await page.waitForTimeout(500);

    await showPage.verifyTimerSection();

    await showPage.clickResetTimer();
    await page.waitForTimeout(500);
  });

  test('all custodianships are created as draft by default', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('New Custodianship');
    await formPage.fillMessage('Should be draft');
    await formPage.addRecipient('recipient@example.com');

    await formPage.clickSave();

    await showPage.verifyStatus('draft');
  });
});
