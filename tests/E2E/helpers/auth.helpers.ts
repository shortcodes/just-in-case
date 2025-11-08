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

  // Debug: log page title and URL
  if (process.env.CI) {
    const title = await page.title();
    const url = page.url();
    console.log(`Login page - Title: "${title}", URL: "${url}"`);

    // Check if email field exists
    const emailExists = await page.locator('#email').count();
    console.log(`Email field count: ${emailExists}`);

    if (emailExists === 0) {
      const bodyText = await page.locator('body').textContent();
      console.log(`Page body text (first 500 chars): ${bodyText?.substring(0, 500)}`);
    }
  }

  await page.fill('#email', credentials.email);
  await page.fill('#password', credentials.password);

  const navigationPromise = page.waitForURL(/\/custodianships|\/dashboard/);
  await page.click('button[type="submit"]');

  if (process.env.CI) {
    // Wait a bit and check where we ended up
    await page.waitForTimeout(2000);
    const currentUrl = page.url();
    console.log(`After login click, current URL: "${currentUrl}"`);

    // Check for validation errors
    const errorText = await page.locator('.text-sm.text-red-600, [role="alert"]').allTextContents();
    if (errorText.length > 0) {
      console.log(`Validation errors: ${JSON.stringify(errorText)}`);
    }

    // Check if email field still exists (would mean we're still on login page)
    const stillOnLogin = await page.locator('#email').count();
    console.log(`Still on login page: ${stillOnLogin > 0}`);
  }

  await navigationPromise;
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
  // Don't pass password to factory - let it use the default hashed 'password'
  // The factory uses Hash::make('password') which can't be overridden with plain text
  return await laravel.factory('App\\Models\\User', {
    name: credentials.name,
    email: credentials.email,
    email_verified_at: new Date().toISOString()
  });
}

export async function loginAsUser(page: Page, laravel: Laravel, user?: any): Promise<any> {
  if (!user) {
    user = await createAuthenticatedUser(laravel);
  }

  if (process.env.CI) {
    console.log(`Created user: email=${user.email}, id=${user.id}`);
    // Verify user exists in database
    const dbUser = await laravel.select(`SELECT id, email, password FROM users WHERE email = '${user.email}'`);
    console.log(`User in database: ${JSON.stringify(dbUser)}`);
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
