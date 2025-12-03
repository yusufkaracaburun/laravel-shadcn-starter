/**
 * API Endpoints Enum
 * Provides type-safe endpoint constants
 */
export enum ApiEndpoints {
  // Sanctum
  CSRF_COOKIE = '/sanctum/csrf-cookie',

  // Auth
  LOGIN = '/login',
  LOGOUT = '/logout',
  REGISTER = '/register',

  // User
  USER_CURRENT = '/api/user/current',
}

