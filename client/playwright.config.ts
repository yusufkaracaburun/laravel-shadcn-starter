import { defineConfig, devices } from '@playwright/test'
// @ts-expect-error - process is a Node.js global, types are provided by @types/node
import process from 'node:process'

const baseURL = process.env.PLAYWRIGHT_TEST_BASE_URL || 'http://localhost:5173'
const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'

export default defineConfig({
  testDir: '.',
  testMatch: [
    /tests\/.*\.(e2e|api)\.spec\.ts$/,
  ],
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
      testMatch: [
        /tests\/.*\.e2e\.spec\.ts$/,
      ],
    },
    {
      name: 'e2e-unauth',
      use: {
        ...devices['Desktop Chrome'],
      },
      testMatch: [
        /tests\/.*\.e2e\.spec\.ts$/,
      ],
    },
    {
      name: 'api',
      use: { ...devices['Desktop Chrome'] },
      testMatch: /tests\/.*\.api\.spec\.ts$/,
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
      command: 'cd ../api && php artisan serve',
      url: `${apiURL}/sanctum/csrf-cookie`,
      reuseExistingServer: true,
      timeout: 20000,
      stdout: 'ignore',
      stderr: 'pipe',
    },
  ],
  globalSetup: undefined,
  globalTeardown: undefined,
})

export { apiURL }
