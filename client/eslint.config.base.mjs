/**
 * Shared ESLint base configuration
 * Contains common settings, ignores, and rules shared between app and test configs
 */

import antfu from '@antfu/eslint-config'
import pluginQuery from '@tanstack/eslint-plugin-query'

// Export the base config composer
// This will be used by app and test configs via antfu's append() method
export default antfu({
  type: 'app',
  vue: true,
  typescript: true,
  formatters: {
    css: true,
    html: true,
    markdown: 'prettier',
  },
  ignorePatterns: [
    '**/node_modules/**',
    '**/dist/**',
    '**/build/**',
    '**/lib/**',
    '**/components/ui/**',
  ],
  ignores: ['**/node_modules/**', '**/dist/**', '**/build/**', '**/lib/**', '**/components/ui/**'],
  settings: {
    'import/core-modules': ['vue-router/auto-routes'],
  },
  globals: {
    definePage: 'readonly',
  },
  rules: {
    'perfectionist/sort-imports': [
      'error',
      {
        tsconfigRootDir: '.',
      },
    ],
  },
  ...pluginQuery.configs['flat/recommended'],
})
