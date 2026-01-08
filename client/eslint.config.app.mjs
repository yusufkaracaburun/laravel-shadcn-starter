/**
 * ESLint configuration for application source files (src/)
 * Returns app-specific config object (not a composer)
 */

export default {
  // App-specific file patterns
  files: ['src/**/*.{ts,tsx,vue}', '*.config.{ts,mjs}', 'vite.config.ts'],
  rules: {
    // Configure operator-linebreak to match Prettier (operators at end of line)
    // This prevents conflicts between ESLint and Prettier formatting
    '@stylistic/operator-linebreak': [
      'error',
      'after',
      {
        overrides: {
          '?': 'before',
          ':': 'before',
        },
      },
    ],
  },
}
