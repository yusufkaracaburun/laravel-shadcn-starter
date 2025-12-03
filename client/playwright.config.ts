import { defineConfig, devices } from '@playwright/test'
import process from 'node:process'

const apiURL = process.env.VITE_API_BASE_URL
const baseURL = process.env.VITE_FRONTEND_URL

export default defineConfig({
  testDir: './tests',
  testMatch: [/tests\/.*\.(e2e|api)\.spec\.ts$/],
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [['html', { open: 'only-on-failure' }], ['list']],
  timeout: 30000,
  use: {
    baseURL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 10000,
    navigationTimeout: 10000,
    extraHTTPHeaders: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    ignoreHTTPSErrors: true,
  },
  projects: [
    {
      name: 'setup',
      testMatch: /.*\.setup\.ts$/,
    },
    {
      name: 'e2e',
      use: {
        ...devices['Desktop Chrome'],
        storageState: '.auth/user.json',
      },
      dependencies: ['setup'],
      testMatch: [/tests\/.*\.e2e\.spec\.ts$/],
    },
    {
      name: 'e2e-unauth',
      use: {
        ...devices['Desktop Chrome'],
      },
      testMatch: [/tests\/.*\.e2e\.spec\.ts$/],
    },
    {
      name: 'api',
      testDir: './tests/api',
      testMatch: [/tests\/api\/.*\.(api|spec)\.spec\.ts$/, /tests\/api\/scenarios\/.*\.spec\.ts$/],
      fullyParallel: false,
      workers: 1,
      use: {
        baseURL: apiURL,
        ...devices['Desktop Chrome'],
        ignoreHTTPSErrors: true,
        extraHTTPHeaders: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      },
    },
  ],
  webServer: [
    {
      command: 'npm run dev',
      url: baseURL,
      reuseExistingServer: true,
      timeout: 15000,
    },
    {
      command: 'cd .. && php artisan serve',
      url: apiURL,
      reuseExistingServer: true,
      timeout: 15000,
    },
  ],
  globalSetup: undefined,
  globalTeardown: undefined,
})

export { apiURL }
