/**
 * Main ESLint configuration
 * Combines base, app, and test configurations
 */

import baseConfig from './eslint.config.base.mjs'
import appConfig from './eslint.config.app.mjs'
import testConfig from './eslint.config.tests.mjs'

// Combine all configs using antfu's append() method
// baseConfig is a FlatConfigComposer, appConfig and testConfig are plain objects
export default baseConfig
  .append(appConfig)
  .append(testConfig)
