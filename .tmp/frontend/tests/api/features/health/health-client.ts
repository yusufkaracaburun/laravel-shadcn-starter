import { APIResponse } from '@playwright/test';
import { BaseClient } from '../shared/core/base-client';

/**
 * Client for health check endpoints
 * Extends BaseClient for consistent request handling
 */
export class HealthClient extends BaseClient {
  /**
   * Health check endpoint
   */
  async check(): Promise<APIResponse> {
    return this.get('/up', { requireCsrf: false });
  }
}

