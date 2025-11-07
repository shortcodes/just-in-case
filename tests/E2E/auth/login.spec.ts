import { test } from '@hyvor/laravel-playwright';
import { expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { DashboardPage } from '../pages/DashboardPage';
import { createAuthenticatedUser } from '../helpers/auth.helpers';
import { resetDatabase } from '../helpers/database.helpers';
import { testUser } from '../fixtures/custodianship.fixtures';

test.describe.configure({ mode: 'serial' });

test.describe('User Login', () => {
  test.beforeAll(async ({ laravel }) => {
    await resetDatabase(laravel);
  });

  test('user can login with valid credentials', async ({ page, laravel }) => {
    const user = await createAuthenticatedUser(laravel, testUser);

    const loginPage = new LoginPage(page);
    await loginPage.goto();

    await loginPage.login(testUser.email, testUser.password);

    await loginPage.verifyRedirectedToDashboard();
  });

  test('user cannot login with invalid credentials', async ({ page, laravel }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();

    await loginPage.login('wrong@example.com', 'wrongpassword');

    await loginPage.verifyErrorMessage(/invalid|incorrect|credentials/i);
  });

  test('user cannot login with incorrect password', async ({ page, laravel }) => {
    const user = await createAuthenticatedUser(laravel, {
      name: 'Test User',
      email: 'correct@example.com',
      password: 'correctpassword'
    });

    const loginPage = new LoginPage(page);
    await loginPage.goto();

    await loginPage.login('correct@example.com', 'wrongpassword');

    await loginPage.verifyErrorMessage(/invalid|incorrect|credentials/i);
  });
});
