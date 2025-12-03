import type { RouteLocationRaw } from 'vue-router'

export const RouterPath: Record<string, RouteLocationRaw> = {
  HOME: '/dashboard',
  LOGIN: '/auth/sign-in',
  REGISTER: '/auth/sign-up',
} as const
