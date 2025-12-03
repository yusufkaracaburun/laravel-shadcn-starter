/**
 * ESLint configuration for application source files (src/)
 * Returns app-specific config object (not a composer)
 */

export default {
  // App-specific file patterns
  files: [
    'src/**/*.{ts,tsx,vue}',
    '*.config.{ts,mjs}',
    'vite.config.ts',
  ],
  rules: {
    // App-specific rules can be added here
    // Currently using base config rules
  },
}
