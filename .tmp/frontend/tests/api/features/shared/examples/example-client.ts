import { APIResponse } from '@playwright/test';
import { BaseClient } from '../core/base-client';

/**
 * Example client demonstrating how to create new API clients
 * This serves as a template for future feature clients
 * 
 * To create a new client:
 * 1. Copy this file to features/{feature}/{feature}-client.ts
 * 2. Rename the class (e.g., ProductsClient)
 * 3. Implement your endpoints using the protected methods from BaseClient
 * 4. Add to ClientFactory in factories/client-factory.ts
 * 5. Create corresponding test file in scenarios/{feature}/
 */
export class ExampleClient extends BaseClient {
  /**
   * Example: Get all items
   */
  async getAll(): Promise<APIResponse> {
    return this.get('/api/example');
  }

  /**
   * Example: Get item by ID
   */
  async getById(id: number): Promise<APIResponse> {
    return this.get(`/api/example/${id}`);
  }

  /**
   * Example: Create new item
   */
  async create(data: Record<string, unknown>): Promise<APIResponse> {
    await this.ensureCsrfCookie();
    return this.post('/api/example', data);
  }

  /**
   * Example: Update item
   */
  async update(id: number, data: Record<string, unknown>): Promise<APIResponse> {
    await this.ensureCsrfCookie();
    return this.put(`/api/example/${id}`, data);
  }

  /**
   * Example: Delete item
   */
  async deleteItem(id: number): Promise<APIResponse> {
    await this.ensureCsrfCookie();
    return this.delete(`/api/example/${id}`);
  }

  /**
   * Example: Custom endpoint with query parameters
   */
  async search(query: string): Promise<APIResponse> {
    return this.get(`/api/example/search?q=${encodeURIComponent(query)}`);
  }
}

