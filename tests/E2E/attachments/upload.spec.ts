import { test } from '@hyvor/laravel-playwright';
import { expect } from '@playwright/test';
import { DashboardPage } from '../pages/DashboardPage';
import { CustodianshipFormPage } from '../pages/CustodianshipFormPage';
import { CustodianshipShowPage } from '../pages/CustodianshipShowPage';
import { loginAsUser } from '../helpers/auth.helpers';
import { resetDatabase } from '../helpers/database.helpers';
import * as fs from 'fs';
import * as path from 'path';

test.describe.configure({ mode: 'serial' });

test.describe('Attachment Upload', () => {
  const testFilesDir = '/tmp/e2e-test-files';

  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);

    if (!fs.existsSync(testFilesDir)) {
      fs.mkdirSync(testFilesDir, { recursive: true });
    }
  });

  test.afterAll(() => {
    if (fs.existsSync(testFilesDir)) {
      fs.rmSync(testFilesDir, { recursive: true, force: true });
    }
  });

  function createTestFile(filename: string, sizeInBytes: number): string {
    const filePath = path.join(testFilesDir, filename);
    const content = 'a'.repeat(sizeInBytes);
    fs.writeFileSync(filePath, content);
    return filePath;
  }

  test('user can upload single attachment', async ({ page, laravel }) => {
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    const testFile = createTestFile('document.txt', 1024 * 100);

    await formPage.fillName('With Single Attachment');
    await formPage.fillMessage('Testing single file upload');
    await formPage.addRecipient('recipient@example.com');

    await formPage.uploadFile(testFile);
    await formPage.waitForUploadComplete();

    await formPage.clickSave();

    await showPage.verifyAttachment('document.txt');
  });

  test('user can upload multiple attachments', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);
    const showPage = new CustodianshipShowPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    const testFile1 = createTestFile('file1.txt', 1024 * 50);
    const testFile2 = createTestFile('file2.txt', 1024 * 50);

    await formPage.fillName('With Multiple Attachments');
    await formPage.fillMessage('Testing multiple files');
    await formPage.addRecipient('recipient@example.com');

    await formPage.uploadMultipleFiles([testFile1, testFile2]);
    await formPage.waitForUploadComplete();

    await formPage.clickSave();

    await showPage.verifyAttachment('file1.txt');
    await showPage.verifyAttachment('file2.txt');
  });

  test('cannot upload attachment exceeding 10MB limit', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    const largeFile = createTestFile('large.txt', 11 * 1024 * 1024);

    await formPage.fillName('Large Attachment Test');
    await formPage.fillMessage('Testing size limit');
    await formPage.addRecipient('recipient@example.com');

    await formPage.uploadFile(largeFile);

    await page.waitForTimeout(1000);

    await formPage.verifyAttachmentSizeError();
  });

  test('user can remove uploaded attachment before saving', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    const testFile = createTestFile('to-remove.txt', 1024 * 100);

    await formPage.fillName('Remove Attachment Test');
    await formPage.fillMessage('Testing removal');
    await formPage.addRecipient('recipient@example.com');

    await formPage.uploadFile(testFile);
    await formPage.waitForUploadComplete();

    await expect(page.locator('#app').getByText('to-remove.txt').first()).toBeVisible();

    await formPage.removeAttachment('to-remove.txt');

    await expect(page.locator('#app').getByText('to-remove.txt')).not.toBeVisible();
  });

  test('total attachment size cannot exceed 10MB across multiple files', async ({ page, laravel }) => {
    await resetDatabase(laravel);
    const user = await loginAsUser(page, laravel);
    const dashboard = new DashboardPage(page);
    const formPage = new CustodianshipFormPage(page);

    await dashboard.goto();
    await dashboard.clickCreateNew();

    const file1 = createTestFile('file1-6mb.txt', 6 * 1024 * 1024);
    const file2 = createTestFile('file2-5mb.txt', 5 * 1024 * 1024);

    await formPage.fillName('Multiple Files Size Test');
    await formPage.fillMessage('Testing total size limit');
    await formPage.addRecipient('recipient@example.com');

    await formPage.uploadFile(file1);
    await formPage.waitForUploadComplete();

    await formPage.uploadFile(file2);

    await page.waitForTimeout(1000);

    await formPage.verifyAttachmentSizeError();
  });
});
