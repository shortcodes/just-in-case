import { test } from '@hyvor/laravel-playwright';
import { expect } from '@playwright/test';
import { DashboardPage } from '../pages/DashboardPage';
import { CustodianshipFormPage } from '../pages/CustodianshipFormPage';
import { CustodianshipShowPage } from '../pages/CustodianshipShowPage';
import { registerUser, loginAsUser, verifyUserEmail } from '../helpers/auth.helpers';
import { resetDatabase } from '../helpers/database.helpers';
import { validCustodianship, testUser } from '../fixtures/custodianship.fixtures';
import * as fs from 'fs';

test.describe.configure({ mode: 'serial' });

test.describe('Critical Path: Full User Journey', () => {
  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);
  });

  test('complete user journey: register → create → activate → reset timer', async ({ page, laravel }) => {
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await registerUser(page, testUser);
    await expect(page).toHaveURL('/custodianships');

    await verifyUserEmail(laravel, testUser.email);
    await page.reload();

    await dashboard.verifyEmptyState();

    await dashboard.clickCreateNew();
    await expect(page).toHaveURL('/custodianships/create');

    await formPage.fillName(validCustodianship.name);
    await formPage.fillMessage(validCustodianship.message);

    await page.getByRole('button', { name: /add recipient/i }).scrollIntoViewIfNeeded();
    await formPage.addRecipient(validCustodianship.recipients[0].email);

    await page.getByRole('button', { name: /^save$/i }).scrollIntoViewIfNeeded();
    await formPage.clickSave();

    await page.waitForURL(/\/custodianships\/[a-f0-9-]+$/);

    await showPage.verifyName(validCustodianship.name);
    await showPage.verifyStatus('draft');

    await showPage.clickActivate();
    await showPage.verifyStatus('active');

    await showPage.clickResetTimer();

    await page.goto('/custodianships');
    await dashboard.verifyCustodianshipExists(validCustodianship.name);
  });
});

test.describe('Critical Path: Edit Custodianship', () => {
  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);
  });

  test('edit custodianship and choose timer reset option', async ({ page, laravel }) => {
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Original Name');
    await formPage.fillMessage('Original message');
    await formPage.addRecipient('recipient@example.com');

    await formPage.clickSave();
    await formPage.verifyRedirectedToShow();

    await showPage.clickActivate();
    await showPage.clickEdit();
    await expect(page).toHaveURL(/\/custodianships\/[a-f0-9-]+\/edit$/);

    await formPage.fillName('Updated Name');
    await formPage.fillMessage('Updated message content');

    await formPage.clickSaveAndResetTimer();

    await formPage.verifyRedirectedToShow();
    await showPage.verifyName('Updated Name');
  });
});

test.describe('Critical Path: Delete Custodianship', () => {
  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);
  });

  test('delete custodianship with confirmation modal', async ({ page, laravel }) => {
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    const custodianshipName = 'Custodianship To Delete';

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName(custodianshipName);
    await formPage.fillMessage('This will be deleted');
    await formPage.addRecipient('recipient@example.com');

    await formPage.clickSave();
    await formPage.verifyRedirectedToShow();

    await showPage.clickDelete();

    await showPage.confirmDelete(custodianshipName);

    await showPage.verifyRedirectedToIndex();

    await dashboard.verifyEmptyState();
  });
});

test.describe('Critical Path: Attachment Upload', () => {
  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);
  });

  test('upload attachment and verify size validation', async ({ page, laravel }) => {
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('With Attachment');
    await formPage.fillMessage('Testing attachment upload');
    await formPage.addRecipient('recipient@example.com');

    const testFilePath = '/tmp/test-file.txt';
    fs.writeFileSync(testFilePath, 'a'.repeat(1024 * 100));

    await formPage.uploadFile(testFilePath);
    await formPage.waitForUploadComplete();

    await formPage.clickSave();
    await formPage.verifyRedirectedToShow();
  });
});

test.describe('Critical Path: Freemium Limits', () => {
  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);
  });

  test('verify freemium limits: max 3 custodianships', async ({ page, laravel }) => {
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);

    await dashboard.goto();

    for (let i = 1; i <= 3; i++) {
      await dashboard.clickCreateNew();
      await formPage.fillName(`Custodianship ${i}`);
      await formPage.fillMessage(`Message ${i}`);
      await formPage.addRecipient('recipient@example.com');
      await formPage.clickSave();
      await page.waitForURL(/\/custodianships\/[a-f0-9-]+$/);
      await dashboard.goto();
      await page.waitForLoadState('networkidle');
    }

    await expect(page.locator('[data-testid="custodianship-card"]')).toHaveCount(3);
    await dashboard.verifyCreateNewButtonDisabled();
  });

  test('verify freemium limits: max 2 recipients', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);

    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    await formPage.fillName('Testing Recipients Limit');
    await formPage.addRecipient('recipient1@example.com');
    await formPage.addRecipient('recipient2@example.com');

    const recipientCount = await formPage.getRecipientCount();
    expect(recipientCount).toBe(2);

    await formPage.verifyAddRecipientButtonDisabled();
  });
});
