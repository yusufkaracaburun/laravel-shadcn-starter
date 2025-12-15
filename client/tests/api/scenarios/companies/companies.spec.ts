import type { Company } from '../../features/companies/company-types'
import type {
  CreateCompanyRequest,
  PaginatedCompaniesResponse,
  UpdateCompanyRequest,
} from '../../features/companies/company-types'

import { HttpStatus } from '../../features/shared/enums'
import {
  expectError,
  expectIResponse,
  expectSuccess,
  expectUnauthenticated,
  expectValidationErrors,
} from '../../features/shared/helpers'
import { CompanyClient } from '../../features/companies/company-client'
import { expect, test } from '../../fixtures'

/**
 * Pure function: Create unique test company data
 * Uses timestamp to ensure uniqueness
 */
function createUniqueTestCompany(baseName = 'Test Company'): CreateCompanyRequest {
  const timestamp = Date.now()
  return {
    name: `${baseName} ${timestamp}`,
    email: `test-company-${timestamp}@example.com`,
    phone: '+1234567890',
    industry: 'technology',
    status: 'active',
    employees: '1-10',
  }
}

/**
 * Pure function: Create update data
 */
function createUpdateData(updates: Partial<UpdateCompanyRequest>): UpdateCompanyRequest {
  return { ...updates }
}

test.describe('Companies API', { tag: ['@api', '@companies'] }, () => {
  test.describe('List Companies', () => {
    test('should get paginated list of companies', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act
      const response = await companyClient.getCompanies(1, 15)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<PaginatedCompaniesResponse>
      const companiesBody = await expectIResponse<PaginatedCompaniesResponse>(response)

      // Assert - Pagination structure
      expect(companiesBody.data).toHaveProperty('data')
      expect(companiesBody.data).toHaveProperty('current_page')
      expect(companiesBody.data).toHaveProperty('per_page')
      expect(companiesBody.data).toHaveProperty('total')
      expect(companiesBody.data).toHaveProperty('last_page')
      expect(companiesBody.data).toHaveProperty('first_page_url')
      expect(companiesBody.data).toHaveProperty('last_page_url')
      expect(companiesBody.data).toHaveProperty('path')

      // Assert - Data should be an array
      expect(Array.isArray(companiesBody.data.data)).toBe(true)

      // Assert - If companies exist, verify structure
      if (companiesBody.data.data.length > 0) {
        const company = companiesBody.data.data[0]
        expect(company).toHaveProperty('id')
        expect(company).toHaveProperty('name')
        expect(company).toHaveProperty('email')
        expect(company).toHaveProperty('industry')
        expect(company).toHaveProperty('status')
        expect(company).toHaveProperty('employees')
      }
    })

    test('should handle pagination parameters', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act - Request page 1 with 5 items per page
      const response = await companyClient.getCompanies(1, 5)

      // Assert
      expectSuccess(response)
      const companiesBody = await expectIResponse<PaginatedCompaniesResponse>(response)
      expect(companiesBody.data.per_page).toBe(5)
      expect(companiesBody.data.current_page).toBe(1)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const companyClient = new CompanyClient(request)

      // Act
      const response = await companyClient.getCompanies()

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Show Company', () => {
    test('should get company by id', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create a company first
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCompanyData = createUniqueTestCompany()
      const createResponse = await companyClient.createCompany(testCompanyData)
      const createdCompany = await expectIResponse<Company>(createResponse)
      const companyId = createdCompany.data.id

      // Act
      const response = await companyClient.getCompany(companyId)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<Company>
      const companyBody = await expectIResponse<Company>(response)

      // Assert - Company data matches
      expect(companyBody.data.id).toBe(companyId)
      expect(companyBody.data).toHaveProperty('name')
      expect(companyBody.data).toHaveProperty('email')
      expect(companyBody.data).toHaveProperty('created_at')
      expect(companyBody.data).toHaveProperty('updated_at')

      // Cleanup
      await companyClient.deleteCompany(companyId)
    })

    test('should return 404 for non-existent company', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentCompanyId = 999999

      // Act
      const response = await companyClient.getCompany(nonExistentCompanyId)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const companyClient = new CompanyClient(request)

      // Act
      const response = await companyClient.getCompany(1)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Create Company', () => {
    test('should create company successfully', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create unique test company data
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCompanyData = createUniqueTestCompany()

      // Act
      const response = await companyClient.createCompany(testCompanyData)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.CREATED)

      // Assert - Response structure matches IResponse<Company>
      const companyBody = await expectIResponse<Company>(response)

      // Assert - Company data matches input
      expect(companyBody.data).toHaveProperty('id')
      expect(companyBody.data.name).toBe(testCompanyData.name)
      expect(companyBody.data.email).toBe(testCompanyData.email)
      expect(companyBody.data.industry).toBe(testCompanyData.industry)
      expect(companyBody.data.status).toBe(testCompanyData.status)
      expect(companyBody.data.employees).toBe(testCompanyData.employees)
      expect(companyBody.data).toHaveProperty('created_at')
      expect(companyBody.data).toHaveProperty('updated_at')

      // Cleanup - Delete created company
      await companyClient.deleteCompany(companyBody.data.id)
    })

    test('should return validation errors for missing name', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateCompanyRequest = {
        name: '',
        email: `test-${Date.now()}@example.com`,
        industry: 'technology',
        status: 'active',
        employees: '1-10',
      }

      // Act
      const response = await companyClient.createCompany(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for invalid email', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateCompanyRequest = {
        name: 'Test Company',
        email: 'invalid-email',
        industry: 'technology',
        status: 'active',
        employees: '1-10',
      }

      // Act
      const response = await companyClient.createCompany(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for invalid industry', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateCompanyRequest = {
        name: 'Test Company',
        email: `test-${Date.now()}@example.com`,
        industry: 'invalid-industry',
        status: 'active',
        employees: '1-10',
      }

      // Act
      const response = await companyClient.createCompany(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for duplicate email', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCompanyData = createUniqueTestCompany()

      // Create first company
      const createResponse = await companyClient.createCompany(testCompanyData)
      expectSuccess(createResponse)
      const createdCompany = await expectIResponse<Company>(createResponse)

      // Try to create another company with same email
      const duplicateData: CreateCompanyRequest = {
        ...testCompanyData,
        name: 'Different Name',
      }

      // Act
      const response = await companyClient.createCompany(duplicateData)

      // Assert
      await expectValidationErrors(response)

      // Cleanup
      await companyClient.deleteCompany(createdCompany.data.id)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      const testCompanyData = createUniqueTestCompany()

      // Act
      const response = await companyClient.createCompany(testCompanyData)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Update Company', () => {
    test('should update company successfully', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create a company first
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCompanyData = createUniqueTestCompany()
      const createResponse = await companyClient.createCompany(testCompanyData)
      const createdCompany = await expectIResponse<Company>(createResponse)
      const companyId = createdCompany.data.id

      // Prepare update data
      const updateData = createUpdateData({
        name: 'Updated Company Name',
      })

      // Act
      const response = await companyClient.updateCompany(companyId, updateData)

      // Assert
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      const companyBody = await expectIResponse<Company>(response)
      expect(companyBody.data.name).toBe('Updated Company Name')
      expect(companyBody.data.email).toBe(testCompanyData.email) // Email should remain unchanged

      // Cleanup
      await companyClient.deleteCompany(companyId)
    })

    test('should update company email', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCompanyData = createUniqueTestCompany()
      const createResponse = await companyClient.createCompany(testCompanyData)
      const createdCompany = await expectIResponse<Company>(createResponse)
      const companyId = createdCompany.data.id

      const newEmail = `updated-${Date.now()}@example.com`
      const updateData = createUpdateData({
        email: newEmail,
      })

      // Act
      const response = await companyClient.updateCompany(companyId, updateData)

      // Assert
      expectSuccess(response)
      const companyBody = await expectIResponse<Company>(response)
      expect(companyBody.data.email).toBe(newEmail)

      // Cleanup
      await companyClient.deleteCompany(companyId)
    })

    test('should return validation errors for invalid email on update', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCompanyData = createUniqueTestCompany()
      const createResponse = await companyClient.createCompany(testCompanyData)
      const createdCompany = await expectIResponse<Company>(createResponse)
      const companyId = createdCompany.data.id

      const updateData = createUpdateData({
        email: 'invalid-email',
      })

      // Act
      const response = await companyClient.updateCompany(companyId, updateData)

      // Assert
      await expectValidationErrors(response)

      // Cleanup
      await companyClient.deleteCompany(companyId)
    })

    test('should return 404 for non-existent company on update', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentCompanyId = 999999
      const updateData = createUpdateData({
        name: 'Updated Name',
      })

      // Act
      const response = await companyClient.updateCompany(nonExistentCompanyId, updateData)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      const updateData = createUpdateData({
        name: 'Updated Name',
      })

      // Act
      const response = await companyClient.updateCompany(1, updateData)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Delete Company', () => {
    test('should delete company successfully', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create a company first
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCompanyData = createUniqueTestCompany()
      const createResponse = await companyClient.createCompany(testCompanyData)
      const createdCompany = await expectIResponse<Company>(createResponse)
      const companyId = createdCompany.data.id

      // Act
      const response = await companyClient.deleteCompany(companyId)

      // Assert - Should return 204 No Content or 200 OK
      expect([HttpStatus.OK, HttpStatus.NO_CONTENT]).toContain(response.status())

      // Verify company is deleted
      const getResponse = await companyClient.getCompany(companyId)
      expectError(getResponse, HttpStatus.NOT_FOUND)
    })

    test('should return 404 for non-existent company on delete', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const companyClient = new CompanyClient(request)
      companyClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentCompanyId = 999999

      // Act
      const response = await companyClient.deleteCompany(nonExistentCompanyId)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const companyClient = new CompanyClient(request)

      // Act
      const response = await companyClient.deleteCompany(1)

      // Assert
      expectUnauthenticated(response)
    })
  })
})
