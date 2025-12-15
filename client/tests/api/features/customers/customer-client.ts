import type { APIResponse } from '@playwright/test'

import type {
  CreateCustomerRequest,
  UpdateCustomerRequest,
  Customer,
  PaginatedCustomersResponse,
} from './customer-types'
import type { IResponse } from '../shared/types'
import { BaseClient } from '../shared/core/base-client'

export class CustomerClient extends BaseClient {
  /**
   * Get paginated list of customers
   * @param page - Page number (default: 1)
   * @param perPage - Number of items per page (default: 15)
   * Returns raw APIResponse
   */
  async getCustomers(page: number = 1, perPage: number = 15): Promise<APIResponse> {
    const endpoint = `/api/customers?page=${page}&per_page=${perPage}`
    return this.get(endpoint)
  }

  /**
   * Get paginated list of customers with typed response
   * @param page - Page number (default: 1)
   * @param perPage - Number of items per page (default: 15)
   * Returns IResponse<PaginatedCustomersResponse>
   */
  async getCustomersTyped(
    page: number = 1,
    perPage: number = 15,
  ): Promise<IResponse<PaginatedCustomersResponse>> {
    const response = await this.getCustomers(page, perPage)
    return response.json() as Promise<IResponse<PaginatedCustomersResponse>>
  }

  /**
   * Get a specific customer by ID
   * @param customerId - Customer ID
   * Returns raw APIResponse
   */
  async getCustomer(customerId: number): Promise<APIResponse> {
    const endpoint = `/api/customers/${customerId}`
    return this.get(endpoint)
  }

  /**
   * Get a specific customer by ID with typed response
   * @param customerId - Customer ID
   * Returns IResponse<Customer>
   */
  async getCustomerTyped(customerId: number): Promise<IResponse<Customer>> {
    const response = await this.getCustomer(customerId)
    return response.json() as Promise<IResponse<Customer>>
  }

  /**
   * Create a new customer
   * @param data - Customer creation data
   * Returns raw APIResponse
   */
  async createCustomer(data: CreateCustomerRequest): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    return this.post('/api/customers', data)
  }

  /**
   * Create a new customer with typed response
   * @param data - Customer creation data
   * Returns IResponse<Customer>
   */
  async createCustomerTyped(data: CreateCustomerRequest): Promise<IResponse<Customer>> {
    const response = await this.createCustomer(data)
    return response.json() as Promise<IResponse<Customer>>
  }

  /**
   * Update an existing customer
   * @param customerId - Customer ID
   * @param data - Customer update data
   * Returns raw APIResponse
   */
  async updateCustomer(customerId: number, data: UpdateCustomerRequest): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    const endpoint = `/api/customers/${customerId}`
    return this.put(endpoint, data)
  }

  /**
   * Update an existing customer with typed response
   * @param customerId - Customer ID
   * @param data - Customer update data
   * Returns IResponse<Customer>
   */
  async updateCustomerTyped(
    customerId: number,
    data: UpdateCustomerRequest,
  ): Promise<IResponse<Customer>> {
    const response = await this.updateCustomer(customerId, data)
    return response.json() as Promise<IResponse<Customer>>
  }

  /**
   * Delete a customer
   * @param customerId - Customer ID
   * Returns raw APIResponse
   */
  async deleteCustomer(customerId: number): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    const endpoint = `/api/customers/${customerId}`
    return this.delete(endpoint)
  }

  /**
   * Copy authentication state from another client
   * Public wrapper for protected BaseClient method
   */
  public copyAuthStateFrom(other: BaseClient): void {
    // Access protected members through type assertion
    // This allows tests to copy auth state between clients
    const thisClient = this as any
    const otherClient = other as any
    thisClient.cookieHandler.setCookies(otherClient.cookieHandler.getCookies())
    thisClient.csrfHandler.setToken(otherClient.csrfHandler.getToken())
  }
}
