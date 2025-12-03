/**
 * ESLint configuration for test files
 * This configuration provides relaxed rules for test files to allow
 * common testing patterns like mocks, any types, and test-specific utilities.
 */

export default {
  // Test-specific file patterns
  files: [
    '**/*.test.{ts,tsx}',
    '**/*.spec.{ts,tsx}',
    '**/tests/**/*.{ts,tsx}',
    '**/test/**/*.{ts,tsx}',
    '**/__tests__/**/*.{ts,tsx}',
    '**/__mocks__/**/*.{ts,tsx}',
    '**/*.setup.{ts,tsx}',
    '**/*.fixture.{ts,tsx}',
    'playwright.config.ts',
    'vitest.config.ts',
  ],
  rules: {
    // Allow unused variables in tests (common for mocks and test data)
    '@typescript-eslint/no-unused-vars': ['warn', {
      argsIgnorePattern: '^_',
      varsIgnorePattern: '^_',
      caughtErrorsIgnorePattern: '^_',
    }],
    // Allow explicit any in tests (needed for mocks and test utilities)
    '@typescript-eslint/no-explicit-any': 'off',
    // Allow non-null assertions in tests (common for test assertions)
    '@typescript-eslint/no-non-null-assertion': 'off',
    // Allow empty functions in tests (common for mocks)
    '@typescript-eslint/no-empty-function': 'off',
    // Allow console statements in tests (useful for debugging)
    'no-console': 'off',
    // Allow unused expressions in tests (common for expect statements)
    'no-unused-expressions': 'off',
    // Allow magic numbers in tests (test data often uses magic numbers)
    'no-magic-numbers': 'off',
    // Allow @ts-expect-error and @ts-ignore in tests (needed for testing edge cases)
    '@typescript-eslint/ban-ts-comment': ['warn', {
      'ts-expect-error': 'allow-with-description',
      'ts-ignore': 'allow-with-description',
      'ts-nocheck': false,
      'ts-check': false,
    }],
    // Allow require statements in tests (some test utilities may need it)
    '@typescript-eslint/no-require-imports': 'off',
    // Relax import rules for test files
    'import/no-extraneous-dependencies': 'off',
    // Relax import sorting for tests (test files often have different import patterns)
    'perfectionist/sort-imports': ['warn', {
      tsconfigRootDir: '.',
    }],
    // Allow trailing spaces in tests (less strict formatting)
    'style/no-trailing-spaces': 'warn',
    // Allow multiple empty lines in tests (test organization)
    'style/no-multiple-empty-lines': ['warn', {
      max: 2,
      maxEOF: 1,
    }],
  },
  languageOptions: {
    globals: {
      // Vitest globals (when globals: true in vitest.config.ts)
      describe: 'readonly',
      it: 'readonly',
      test: 'readonly',
      expect: 'readonly',
      beforeEach: 'readonly',
      afterEach: 'readonly',
      beforeAll: 'readonly',
      afterAll: 'readonly',
      vi: 'readonly',
      vitest: 'readonly',
      // Playwright globals
      page: 'readonly',
      context: 'readonly',
      browser: 'readonly',
      request: 'readonly',
    },
  },
}
