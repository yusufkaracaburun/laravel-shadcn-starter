import { mergeTests } from '@playwright/test'

import { loginTestFixture, registerTestFixture } from '../features'

export const $test = mergeTests(loginTestFixture, registerTestFixture)
