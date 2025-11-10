import { defineConfig, devices } from '@playwright/test';
import type { LaravelOptions } from '@hyvor/laravel-playwright';

export default defineConfig<LaravelOptions>({
  testDir: './tests/E2E',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: 'list',

  use: {
    baseURL: process.env.APP_URL || 'http://localhost',
    laravelBaseUrl: `${process.env.APP_URL || 'http://localhost'}/playwright`,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    laravelEnv: {
      APP_LOCALE: 'en',
      DB_CONNECTION: 'sqlite',
      DB_DATABASE: '/var/www/html/database/e2e.sqlite',
    },
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
