import { Page } from '@playwright/test';
import { Laravel } from '@hyvor/laravel-playwright';

export interface UserCredentials {
  name?: string;
  email: string;
  password: string;
}

export async function registerUser(page: Page, credentials: UserCredentials): Promise<void> {
  await page.goto('/register');

  if (credentials.name) {
    await page.fill('#name', credentials.name);
  }
  await page.fill('#email', credentials.email);
  await page.fill('#password', credentials.password);
  await page.fill('#password_confirmation', credentials.password);

  const navigationPromise = page.waitForURL(/\/custodianships|\/dashboard/);
  await page.click('button[type="submit"]');
  await navigationPromise;
}

export async function loginUser(page: Page, credentials: Pick<UserCredentials, 'email' | 'password'>): Promise<void> {
  await page.goto('/login');

  await page.fill('#email', credentials.email);
  await page.fill('#password', credentials.password);

  await page.click('button[type="submit"]');
  await page.waitForURL(/\/custodianships|\/dashboard/);
}

export async function logoutUser(page: Page): Promise<void> {
  await page.click('[data-testid="user-menu"]');
  await page.click('[data-testid="logout-button"]');
}

export async function createAuthenticatedUser(
  laravel: Laravel,
  credentials: UserCredentials = {
    name: 'Test User',
    email: 'test@example.com',
    password: 'password'
  }
): Promise<any> {
  // Use factory with withKnownPassword state for consistent password hashing
  return await laravel.factory('App\\Models\\User', {
    name: credentials.name,
    email: credentials.email,
    email_verified_at: new Date().toISOString()
  }, 'withKnownPassword');
}

export async function loginAsUser(page: Page, laravel: Laravel, user?: any): Promise<any> {
  if (!user) {
    user = await createAuthenticatedUser(laravel);
  }

  await loginUser(page, {
    email: user.email,
    password: 'password' // Must match the factory's default password
  });

  return user;
}

export async function verifyUserEmail(laravel: Laravel, email: string): Promise<void> {
  await laravel.query('UPDATE users SET email_verified_at = NOW() WHERE email = ?', [email]);
}
