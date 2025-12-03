/**
 * Global type declarations for test files
 * Provides Node.js globals like `process` for Playwright and Vitest tests
 */

declare global {
  // Node.js process global (available in Node.js test environments)

  const process: NodeJS.Process
}

export {}

