/**
 * ESLint configuration for application source files (src/)
 * Returns app-specific config object (not a composer)
 */

export default {
  // App-specific file patterns
  files: ['src/**/*.{ts,tsx,vue}', '*.config.{ts,mjs}', 'vite.config.ts'],
  rules: {
    // Disable operator-linebreak rules - Prettier handles formatting
    '@stylistic/operator-linebreak': 'off',
    'vue/operator-linebreak': 'off',
  },
}
