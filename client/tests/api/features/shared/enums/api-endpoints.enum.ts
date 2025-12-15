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
  USER_ROLES = '/api/user/roles',
  USER_LIST = '/api/user',
  USER_SHOW = '/api/user', // Template: append /{id}
  USER_CREATE = '/api/user',
  USER_UPDATE = '/api/user', // Template: append /{id}
  USER_DELETE = '/api/user', // Template: append /{id}

  // Project
  PROJECT_LIST = '/api/project',
  PROJECT_SHOW = '/api/project', // Template: append /{id}
  PROJECT_CREATE = '/api/project',
  PROJECT_UPDATE = '/api/project', // Template: append /{id}
  PROJECT_DELETE = '/api/project', // Template: append /{id}

  // Item
  ITEM_LIST = '/api/items',
  ITEM_SHOW = '/api/items', // Template: append /{id}
  ITEM_CREATE = '/api/items',
  ITEM_UPDATE = '/api/items', // Template: append /{id}
  ITEM_DELETE = '/api/items', // Template: append /{id}
}
