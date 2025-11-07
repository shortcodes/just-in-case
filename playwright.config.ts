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
    baseURL: 'http://localhost',
    laravelBaseUrl: 'http://localhost/playwright',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    laravelEnv: {
      APP_LOCALE: 'en',
    },
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
