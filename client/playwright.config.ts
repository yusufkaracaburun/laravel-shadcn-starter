import { defineConfig, devices } from '@playwright/test'
import process from 'node:process'
import { defineBddConfig } from 'playwright-bdd'

const baseURL = process.env.PLAYWRIGHT_TEST_BASE_URL || 'http://localhost:5173'
const apiURL = process.env.PLAYWRIGHT_TEST_API_URL || 'http://127.0.0.1:8000'

// Configure BDD - this generates test files from feature files
defineBddConfig({
  features: './tests/e2e/features/**/*.feature',
  steps: ['./tests/e2e/steps/**/*.ts', './tests/e2e/fixtures.ts'],
  outputDir: '.features-gen',
  featuresRoot: './tests/e2e/features',
})

export default defineConfig({
  testDir: '.',
  testMatch: [
    /tests\/.*\.(e2e|api)\.spec\.ts$/,
    /\.features-gen\/.*\.spec\.(js|ts)$/,
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
    // Setup project - authenticates once before all tests
    {
      name: 'setup',
      testMatch: /.*\.setup\.ts$/,
    },
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        // Use prepared auth state for all E2E tests
        storageState: 'playwright/.auth/user.json',
      },
      dependencies: ['setup'],
      testMatch: [
        /tests\/.*\.e2e\.spec\.ts$/,
        /\.features-gen\/.*\.spec\.(js|ts)$/,
      ],
    },
    {
      name: 'api',
      use: { ...devices['Desktop Chrome'] },
      testMatch: /tests\/.*\.api\.spec\.ts$/,
      // API tests don't need storage state
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
