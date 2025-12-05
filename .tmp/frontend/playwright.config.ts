import * as dotenvx from '@dotenvx/dotenvx'
import { defineConfig, devices } from '@playwright/test'
import * as path from 'node:path'
import * as process from 'node:process'

dotenvx.config({ path: path.resolve(process.cwd(), '.env') })

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: './tests',
  testMatch: [
    /.*\.spec\.ts$/,
    /.*\.(e2e|api)\.spec\.ts$/,
  ],
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [['html', { open: 'only-on-failure' }], ['list']],
  timeout: 30000,
  use: {
    baseURL: process.env.VITE_API_BASE_URL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 10000,
    navigationTimeout: 10000,
    extraHTTPHeaders: {
      Accept: 'application/json',
    },
    ignoreHTTPSErrors: true,
  },

  projects: [
    {
      name: 'api',
      testDir: './tests/api/scenarios',
      use: {
        baseURL: process.env.VITE_API_BASE_URL,
        ...devices['Desktop Chrome'],
      },
    },
  ],

  /* Run your local dev server before starting the tests */
  // webServer: {
  //   command: 'npm run start',
  //   url: 'http://127.0.0.1:3000',
  //   reuseExistingServer: !process.env.CI,
  // },
})
