import { config } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, vi } from 'vitest'

// Mock router
const mockRouter = {
  push: vi.fn(),
  replace: vi.fn(),
  currentRoute: {
    value: {
      query: {},
      path: '/',
    },
  },
}

// Mock vue-router before any imports
vi.mock('vue-router', async () => {
  const actual = await vi.importActual('vue-router')
  return {
    ...actual,
    useRouter: () => mockRouter,
    useRoute: () => mockRouter.currentRoute.value,
  }
})

// Setup Pinia before each test
beforeEach(() => {
  setActivePinia(createPinia())
})

// Global test utilities
config.global.mocks = {
  $router: mockRouter,
  $route: mockRouter.currentRoute.value,
}

// Auto-import global components stub
config.global.stubs = {
  UiSpinner: true,
  Transition: true,
  TransitionGroup: true,
}

// Make ref available globally for auto-imports
global.ref = (val: any) => ({ value: val })
