/**
 * Main fixtures entry point
 * Re-exports Playwright test fixtures for convenient importing
 * 
 * Usage:
 * ```typescript
 * import { test, expect } from '../../fixtures';
 * ```
 */
export { test, expect } from './api';

/**
 * Re-export test data fixtures
 */
export * from './data/user';

