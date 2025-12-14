import type { APIResponse } from '@playwright/test'

import type { CreateCompanyRequest, UpdateCompanyRequest, Company, PaginatedCompaniesResponse } from './company-types'
import type { IResponse } from '../shared/types'
import { BaseClient } from '../shared/core/base-client'

export class CompanyClient extends BaseClient {
  /**
   * Get paginated list of companies
   * @param page - Page number (default: 1)
   * @param perPage - Number of items per page (default: 15)
   * Returns raw APIResponse
   */
  async getCompanies(page: number = 1, perPage: number = 15): Promise<APIResponse> {
    const endpoint = `/api/company?page=${page}&per_page=${perPage}`
    return this.get(endpoint)
  }

  /**
   * Get paginated list of companies with typed response
   * @param page - Page number (default: 1)
   * @param perPage - Number of items per page (default: 15)
   * Returns IResponse<PaginatedCompaniesResponse>
   */
  async getCompaniesTyped(page: number = 1, perPage: number = 15): Promise<IResponse<PaginatedCompaniesResponse>> {
    const response = await this.getCompanies(page, perPage)
    return response.json() as Promise<IResponse<PaginatedCompaniesResponse>>
  }

  /**
   * Get a specific company by ID
   * @param companyId - Company ID
   * Returns raw APIResponse
   */
  async getCompany(companyId: number): Promise<APIResponse> {
    const endpoint = `/api/company/${companyId}`
    return this.get(endpoint)
  }

  /**
   * Get a specific company by ID with typed response
   * @param companyId - Company ID
   * Returns IResponse<Company>
   */
  async getCompanyTyped(companyId: number): Promise<IResponse<Company>> {
    const response = await this.getCompany(companyId)
    return response.json() as Promise<IResponse<Company>>
  }

  /**
   * Create a new company
   * @param data - Company creation data
   * Returns raw APIResponse
   */
  async createCompany(data: CreateCompanyRequest): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    return this.post('/api/company', data)
  }

  /**
   * Create a new company with typed response
   * @param data - Company creation data
   * Returns IResponse<Company>
   */
  async createCompanyTyped(data: CreateCompanyRequest): Promise<IResponse<Company>> {
    const response = await this.createCompany(data)
    return response.json() as Promise<IResponse<Company>>
  }

  /**
   * Update an existing company
   * @param companyId - Company ID
   * @param data - Company update data
   * Returns raw APIResponse
   */
  async updateCompany(companyId: number, data: UpdateCompanyRequest): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    const endpoint = `/api/company/${companyId}`
    return this.put(endpoint, data)
  }

  /**
   * Update an existing company with typed response
   * @param companyId - Company ID
   * @param data - Company update data
   * Returns IResponse<Company>
   */
  async updateCompanyTyped(companyId: number, data: UpdateCompanyRequest): Promise<IResponse<Company>> {
    const response = await this.updateCompany(companyId, data)
    return response.json() as Promise<IResponse<Company>>
  }

  /**
   * Delete a company
   * @param companyId - Company ID
   * Returns raw APIResponse
   */
  async deleteCompany(companyId: number): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    const endpoint = `/api/company/${companyId}`
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
