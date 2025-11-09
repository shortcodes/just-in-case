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
  // Create user directly via query to bypass model's password hashing cast
  // This is the bcrypt hash for 'password' with cost=10
  const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
  await laravel.query(
    'INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)',
    [
      credentials.name,
      credentials.email,
      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
      now,
      now,
      now
    ]
  );

  // Retrieve the created user
  const users = await laravel.query('SELECT id, name, email FROM users WHERE email = ?', [credentials.email]);
  return users[0];
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
