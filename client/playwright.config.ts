import { defineConfig, devices } from '@playwright/test'
import process from 'node:process'

const baseURL = process.env.PLAYWRIGHT_TEST_BASE_URL || 'http://localhost:5173'
const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'

export default defineConfig({
  testDir: './tests',
  testMatch: /.*\.(e2e|api)\.spec\.ts$/,
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [['html', { open: 'only-on-failure' }], ['list']],
  outputDir: './test-results',
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
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  // webServer: [
  //   {
  //     command: 'npm run dev',
  //     url: baseURL,
  //     reuseExistingServer: true,
  //     timeout: 10000,
  //   },
  //   {
  //     command: 'cd ../api && php artisan serve',
  //     url: apiURL,
  //     reuseExistingServer: true,
  //     timeout: 10000,
  //     stdout: 'ignore',
  //     stderr: 'pipe',
  //   },
  // ],
  globalSetup: undefined,
  globalTeardown: undefined,
})

export { apiURL }
