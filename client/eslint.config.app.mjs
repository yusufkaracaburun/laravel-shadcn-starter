/**
 * ESLint configuration for application source files (src/)
 * Returns app-specific config object (not a composer)
 */

export default {
  // App-specific file patterns
  files: ['src/**/*.{ts,tsx,vue}', '*.config.{ts,mjs}', 'vite.config.ts'],
  rules: {
    '@stylistic/operator-linebreak': [
      'warn',
      'before',
      {
        overrides: {
          '=': 'after',
          '?': 'after',
          ':': 'after',
          '&&': 'after',
          '||': 'after',
          '??': 'after',
        },
      },
    ],
    'vue/operator-linebreak': [
      'warn',
      'before',
      {
        overrides: {
          '=': 'after',
          '?': 'after',
          ':': 'after',
          '&&': 'after',
          '||': 'after',
          '??': 'after',
        },
      },
    ],
    '@stylistic/brace-style': 'off',
  },
}
