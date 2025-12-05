import { APIResponse } from '@playwright/test';
import { BaseClient } from '../shared/core/base-client';
import type { User } from '../shared/types';

/**
 * Client for authentication-related API endpoints
 * Extends BaseClient for automatic CSRF and header handling
 */
export class AuthClient extends BaseClient {
  /**
   * Get CSRF cookie from Sanctum (required before login)
   */
  async getCsrfCookie(): Promise<APIResponse> {
    return this.get('/sanctum/csrf-cookie', { requireCsrf: false });
  }

  /**
   * Login with email and password
   */
  async login(email: string, password: string): Promise<APIResponse> {
    await this.ensureCsrfCookie();
    return this.post('/api/login', { email, password });
  }

  /**
   * Get authenticated user
   */
  async getUser(): Promise<APIResponse> {
    return this.get('/api/user');
  }

  /**
   * Get authenticated user with typed response
   */
  async getUserTyped(): Promise<User> {
    const response = await this.getUser();
    return response.json() as Promise<User>;
  }

  /**
   * Logout user
   */
  async logout(): Promise<APIResponse> {
    return this.post('/api/logout', {});
  }
}

