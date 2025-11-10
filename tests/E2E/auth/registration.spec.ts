import { test } from '@hyvor/laravel-playwright';
import { expect } from '@playwright/test';
import { resetDatabase } from '../helpers/database.helpers';

test.describe.configure({ mode: 'serial' });

test.describe('User Registration', () => {
  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);
  });

  test('user can register with valid credentials', async ({ page }) => {
    await page.goto('/register');

    await page.fill('#name', 'Test User');
    await page.fill('#email', 'test@example.com');
    await page.fill('#password', 'password123');
    await page.fill('#password_confirmation', 'password123');

    await page.check('#terms_accepted');
    await page.check('#not_testament_acknowledged');

    await page.click('button[type="submit"]');

    await expect(page).toHaveURL('/custodianships');
  });

  test('user cannot register with existing email', async ({ page, laravel }) => {
    await laravel.factory('App\\Models\\User', {
      email: 'existing@example.com',
    });

    await page.goto('/register');

    await page.fill('#name', 'Test User');
    await page.fill('#email', 'existing@example.com');
    await page.fill('#password', 'password123');
    await page.fill('#password_confirmation', 'password123');

    await page.check('#terms_accepted');
    await page.check('#not_testament_acknowledged');

    await page.click('button[type="submit"]');

    await expect(page.locator('#app').getByText('The email address has already been taken')).toBeVisible();
  });

  test('user cannot register without accepting terms', async ({ page }) => {
    await page.goto('/register');

    await page.fill('#name', 'Test User');
    await page.fill('#email', 'test2@example.com');
    await page.fill('#password', 'password123');
    await page.fill('#password_confirmation', 'password123');

    await page.check('#not_testament_acknowledged');

    await page.click('button[type="submit"]');

    await expect(page.locator('#app').getByText('terms and conditions', { exact: false })).toBeVisible();
  });

  test('user cannot register without acknowledging legal disclaimer', async ({ page }) => {
    await page.goto('/register');

    await page.fill('#name', 'Test User');
    await page.fill('#email', 'test3@example.com');
    await page.fill('#password', 'password123');
    await page.fill('#password_confirmation', 'password123');

    await page.check('#terms_accepted');

    await page.click('button[type="submit"]');

    await expect(page.locator('#app').getByText('legal disclaimer', { exact: false })).toBeVisible();
  });
});
