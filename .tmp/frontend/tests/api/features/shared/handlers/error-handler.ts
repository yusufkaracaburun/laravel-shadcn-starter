import { APIResponse } from '@playwright/test';
import type { ValidationError } from '../types/types';

/**
 * Error handler for API responses
 * Provides consistent error handling and parsing
 */
export class ErrorHandler {
  /**
   * Parse error response body
   */
  static async parseError(response: APIResponse): Promise<{
    message: string;
    errors?: Record<string, string[]>;
    status: number;
  }> {
    const body = await response.json().catch(() => ({}));
    return {
      message: (body as { message?: string }).message || 'An error occurred',
      errors: (body as ValidationError).errors,
      status: response.status(),
    };
  }

  /**
   * Check if response is a validation error
   */
  static async isValidationError(response: APIResponse): Promise<boolean> {
    if (response.status() !== 422) return false;
    const body = await response.json().catch(() => ({}));
    return 'errors' in body;
  }

  /**
   * Check if response is an authentication error
   */
  static isAuthenticationError(response: APIResponse): boolean {
    return response.status() === 401;
  }

  /**
   * Check if response is a forbidden error
   */
  static isForbiddenError(response: APIResponse): boolean {
    return response.status() === 403;
  }

  /**
   * Check if response is a not found error
   */
  static isNotFoundError(response: APIResponse): boolean {
    return response.status() === 404;
  }

  /**
   * Check if response is a server error
   */
  static isServerError(response: APIResponse): boolean {
    return response.status() >= 500;
  }
}

