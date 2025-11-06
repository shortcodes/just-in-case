import { test } from '@hyvor/laravel-playwright';
import { expect } from '@playwright/test';

test.describe.configure({ mode: 'serial' });

test.describe('User Registration', () => {
  test.beforeAll(async ({ laravel }) => {
    await laravel.artisan('migrate:fresh');
  });

  test('user can register with valid credentials', async ({ page }) => {
    await page.goto('/register');

    await page.fill('#name', 'Test User');
    await page.fill('#email', 'test@example.com');
    await page.fill('#password', 'password123');
    await page.fill('#password_confirmation', 'password123');

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

    await page.click('button[type="submit"]');

    await expect(page.locator('#app').getByText('The email has already been taken')).toBeVisible();
  });
});
