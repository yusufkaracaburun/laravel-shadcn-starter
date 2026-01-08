/**
 * ESLint configuration for application source files (src/)
 * Returns app-specific config object (not a composer)
 */

export default {
  // App-specific file patterns
  files: ['src/**/*.{ts,tsx,vue}', '*.config.{ts,mjs}', 'vite.config.ts'],
  rules: {
    // Configure operator-linebreak to match Prettier (operators at end of line)
    // 'before' means operator stays at the end of the line before the break
    // This prevents conflicts between ESLint and Prettier formatting
    '@stylistic/operator-linebreak': [
      'error',
      'before',
      {
        overrides: {
          '?': 'before',
          ':': 'before',
        },
      },
    ],
    // Vue-specific operator-linebreak rule (overrides the general rule for Vue files)
    'vue/operator-linebreak': [
      'error',
      'before',
      {
        overrides: {
          '?': 'before',
          ':': 'before',
        },
      },
    ],
  },
}
