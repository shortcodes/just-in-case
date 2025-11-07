import { test } from '@hyvor/laravel-playwright';
import { expect } from '@playwright/test';
import { DashboardPage } from '../pages/DashboardPage';
import { CustodianshipFormPage } from '../pages/CustodianshipFormPage';
import { CustodianshipShowPage } from '../pages/CustodianshipShowPage';
import { loginAsUser } from '../helpers/auth.helpers';
import { resetDatabase } from '../helpers/database.helpers';

test.describe.configure({ mode: 'serial' });

test.describe('Add Recipients', () => {
  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);
  });

  test('user can add single recipient to custodianship', async ({ page, laravel }) => {
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Single Recipient');
    await formPage.fillMessage('Testing single recipient');
    await formPage.addRecipient('single@example.com');

    await formPage.clickSave();

    await showPage.verifyRecipient('single@example.com');
  });

  test('user can add two recipients (max in free plan)', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Two Recipients');
    await formPage.fillMessage('Testing two recipients');
    await formPage.addRecipient('first@example.com');
    await formPage.addRecipient('second@example.com');

    await formPage.clickSave();

    await showPage.verifyRecipient('first@example.com');
    await showPage.verifyRecipient('second@example.com');
  });

  test('user can remove recipient before saving', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Remove Recipient Test');
    await formPage.addRecipient('first@example.com');
    await formPage.addRecipient('second@example.com');

    let recipientCount = await formPage.getRecipientCount();
    expect(recipientCount).toBe(2);

    await formPage.removeRecipient(0);

    recipientCount = await formPage.getRecipientCount();
    expect(recipientCount).toBe(1);
  });

  test('cannot add more than 2 recipients in free plan', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Max Recipients Test');
    await formPage.addRecipient('first@example.com');
    await formPage.addRecipient('second@example.com');

    const recipientCount = await formPage.getRecipientCount();
    expect(recipientCount).toBe(2);

    await formPage.verifyAddRecipientButtonDisabled();
  });

  test('email validation for recipient email addresses', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Invalid Email Test');
    await formPage.fillMessage('Testing email validation');
    await formPage.addRecipient('not-an-email');

    await formPage.clickSave();

    await formPage.verifyRecipientError();
  });
});
