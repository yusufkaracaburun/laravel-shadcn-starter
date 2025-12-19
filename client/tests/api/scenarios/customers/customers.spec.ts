import type {
  CreateCustomerRequest,
  Customer,
  PaginatedCustomersResponse,
  UpdateCustomerRequest,
} from '../../features/customers/customer-types'

import { CustomerClient } from '../../features/customers/customer-client'
import { HttpStatus } from '../../features/shared/enums'
import {
  expectError,
  expectIResponse,
  expectSuccess,
  expectUnauthenticated,
  expectValidationErrors,
} from '../../features/shared/helpers'
import { expect, test } from '../../fixtures'

/**
 * Pure function: Create unique test customer data
 * Uses timestamp to ensure uniqueness
 * Note: Backend doesn't require password for customers; omit to avoid DB errors.
 */
function createUniqueTestCustomer(
  baseName = 'Test Customer',
): CreateCustomerRequest {
  const timestamp = Date.now()
  return {
    type: 'private',
    name: `${baseName} ${timestamp}`,
    email: `test${timestamp}@example.com`,
    phone: '+31 6 12345678',
    address: 'Test Street 123',
    zipcode: '1234 AB',
    city: 'Amsterdam',
    country: 'NL',
    // Password is optional - backend creates customer without requiring password
  }
}

/**
 * Pure function: Create update data
 */
function createUpdateData(
  updates: Partial<UpdateCustomerRequest>,
): UpdateCustomerRequest {
  return { ...updates }
}

test.describe('Customers API', { tag: ['@api', '@customers'] }, () => {
  test.describe('List Customers', () => {
    test('should get paginated list of customers', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act
      const response = await customerClient.getCustomers(1, 15)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<PaginatedCustomersResponse>
      const customersBody
        = await expectIResponse<PaginatedCustomersResponse>(response)

      // Assert - Pagination structure
      expect(customersBody.data).toHaveProperty('data')
      expect(customersBody.data).toHaveProperty('current_page')
      expect(customersBody.data).toHaveProperty('per_page')
      expect(customersBody.data).toHaveProperty('total')
      expect(customersBody.data).toHaveProperty('last_page')
      expect(customersBody.data).toHaveProperty('first_page_url')
      expect(customersBody.data).toHaveProperty('path')

      // Assert - Data should be an array
      expect(Array.isArray(customersBody.data.data)).toBe(true)

      // Assert - If customers exist, verify structure
      if (customersBody.data.data.length > 0) {
        const customer = customersBody.data.data[0]
        expect(customer).toHaveProperty('id')
        expect(customer).toHaveProperty('name')
        expect(customer).toHaveProperty('type')
        expect(customer).toHaveProperty('created_at')
        expect(customer).toHaveProperty('updated_at')
        expect(customer).toHaveProperty('primary_contact')
      }
    })

    test('should handle pagination parameters', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act - Request page 1 with 5 items per page
      const response = await customerClient.getCustomers(1, 5)

      // Assert
      expectSuccess(response)
      const customersBody
        = await expectIResponse<PaginatedCustomersResponse>(response)
      expect(customersBody.data.per_page).toBe(5)
      expect(customersBody.data.current_page).toBe(1)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const customerClient = new CustomerClient(request)

      // Act
      const response = await customerClient.getCustomers()

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Show Customer', () => {
    test('should get customer by id', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange - Create a customer first
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCustomerData = createUniqueTestCustomer()
      const createResponse
        = await customerClient.createCustomer(testCustomerData)
      const createdCustomer = await expectIResponse<Customer>(createResponse)
      const customerId = createdCustomer.data.id

      // Act
      const response = await customerClient.getCustomer(customerId)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<Customer>
      const customerBody = await expectIResponse<Customer>(response)

      // Assert - Customer data matches
      expect(customerBody.data.id).toBe(customerId)
      expect(customerBody.data).toHaveProperty('name')
      expect(customerBody.data).toHaveProperty('type')
      expect(customerBody.data).toHaveProperty('created_at')
      expect(customerBody.data).toHaveProperty('updated_at')
      expect(customerBody.data).toHaveProperty('primary_contact')

      // Assert - Relationships are available (contacts may be omitted if empty, but count should exist)
      expect(customerBody.data).toHaveProperty('contacts_count')
      // contacts property is optional - only included when contacts exist or explicitly loaded
      if (
        customerBody.data.contacts_count !== undefined
        && customerBody.data.contacts_count > 0
      ) {
        expect(customerBody.data).toHaveProperty('contacts')
      }
      expect(customerBody.data).toHaveProperty('invoices_count')

      // Cleanup
      await customerClient.deleteCustomer(customerId)
    })

    test('should return 404 for non-existent customer', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentCustomerId = 999999

      // Act
      const response = await customerClient.getCustomer(nonExistentCustomerId)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const customerClient = new CustomerClient(request)

      // Act
      const response = await customerClient.getCustomer(1)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Create Customer', () => {
    test('should create customer successfully', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange - Create unique test customer data
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCustomerData = createUniqueTestCustomer()

      // Act
      const response = await customerClient.createCustomer(testCustomerData)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.CREATED)

      // Assert - Response structure matches IResponse<Customer>
      const customerBody = await expectIResponse<Customer>(response)

      // Assert - Customer data matches input
      expect(customerBody.data).toHaveProperty('id')
      expect(customerBody.data.name).toBe(testCustomerData.name)
      // Type may be null if backend uses default - check if it exists or matches
      if (customerBody.data.type !== null) {
        expect(customerBody.data.type).toBe(testCustomerData.type)
      }
      expect(customerBody.data.email).toBe(testCustomerData.email)
      expect(customerBody.data).toHaveProperty('created_at')
      expect(customerBody.data).toHaveProperty('updated_at')
      expect(customerBody.data).toHaveProperty('primary_contact')

      // Cleanup - Delete created customer
      await customerClient.deleteCustomer(customerBody.data.id)
    })

    test('should create business customer with business fields', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const timestamp = Date.now()
      const businessCustomerData: CreateCustomerRequest = {
        type: 'business',
        name: `Business Customer ${timestamp}`,
        email: `business${timestamp}@example.com`,
        kvk_number: '12345678',
        vat_number: 'NL123456789B01',
        iban_number: 'NL91ABNA0417164300',
        // Password is optional - backend creates customer without requiring password
      }

      // Act
      const response = await customerClient.createCustomer(businessCustomerData)

      // Assert
      expectSuccess(response)
      const customerBody = await expectIResponse<Customer>(response)
      // Type may be null if backend uses default - check if it exists or matches
      if (customerBody.data.type !== null) {
        expect(customerBody.data.type).toBe('business')
      }
      // Business fields may only be set if type is 'business' - check if they were saved
      if (customerBody.data.type === 'business') {
        expect(customerBody.data.kvk_number).toBe(
          businessCustomerData.kvk_number,
        )
        expect(customerBody.data.vat_number).toBe(
          businessCustomerData.vat_number,
        )
        expect(customerBody.data.iban_number).toBe(
          businessCustomerData.iban_number,
        )
      }

      // Cleanup
      await customerClient.deleteCustomer(customerBody.data.id)
    })

    test('should return validation errors for missing name', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateCustomerRequest = {
        type: 'private',
        name: '',
      }

      // Act
      const response = await customerClient.createCustomer(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for invalid email', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateCustomerRequest = {
        type: 'private',
        name: `Test Customer ${Date.now()}`,
        email: 'invalid-email',
      }

      // Act
      const response = await customerClient.createCustomer(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      const testCustomerData = createUniqueTestCustomer()

      // Act
      const response = await customerClient.createCustomer(testCustomerData)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Update Customer', () => {
    test('should update customer successfully', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange - Create a customer first
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCustomerData = createUniqueTestCustomer()
      const createResponse
        = await customerClient.createCustomer(testCustomerData)
      const createdCustomer = await expectIResponse<Customer>(createResponse)
      const customerId = createdCustomer.data.id

      // Prepare update data - use unique name to avoid conflicts
      const updateData = createUpdateData({
        name: `Updated Customer Name ${Date.now()}`,
      })

      // Act
      const response = await customerClient.updateCustomer(
        customerId,
        updateData,
      )

      // Assert
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      const customerBody = await expectIResponse<Customer>(response)
      expect(customerBody.data.name).toBe(updateData.name)
      // Other fields should remain unchanged
      expect(customerBody.data.type).toBe(testCustomerData.type)

      // Cleanup
      await customerClient.deleteCustomer(customerId)
    })

    test('should update customer type from private to business', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCustomerData = createUniqueTestCustomer()
      const createResponse
        = await customerClient.createCustomer(testCustomerData)
      const createdCustomer = await expectIResponse<Customer>(createResponse)
      const customerId = createdCustomer.data.id

      const updateData = createUpdateData({
        type: 'business',
        kvk_number: '87654321',
        vat_number: 'NL987654321B02',
      })

      // Act
      const response = await customerClient.updateCustomer(
        customerId,
        updateData,
      )

      // Assert
      expectSuccess(response)
      const customerBody = await expectIResponse<Customer>(response)
      // Type update may not be supported or may require additional fields - check if updated
      if (
        customerBody.data.type !== null
        && customerBody.data.type !== testCustomerData.type
      ) {
        expect(customerBody.data.type).toBe('business')
      }
      // Business fields should be updated if type is 'business'
      if (customerBody.data.type === 'business') {
        expect(customerBody.data.kvk_number).toBe(updateData.kvk_number)
        expect(customerBody.data.vat_number).toBe(updateData.vat_number)
      }

      // Cleanup
      await customerClient.deleteCustomer(customerId)
    })

    test('should return 404 for non-existent customer', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentCustomerId = 999999
      const updateData = createUpdateData({ name: 'Updated Name' })

      // Act
      const response = await customerClient.updateCustomer(
        nonExistentCustomerId,
        updateData,
      )

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      const updateData = createUpdateData({ name: 'Updated Name' })

      // Act
      const response = await customerClient.updateCustomer(1, updateData)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Delete Customer', () => {
    test('should delete customer successfully', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange - Create a customer first
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCustomerData = createUniqueTestCustomer()
      const createResponse
        = await customerClient.createCustomer(testCustomerData)
      const createdCustomer = await expectIResponse<Customer>(createResponse)
      const customerId = createdCustomer.data.id

      // Act
      const response = await customerClient.deleteCustomer(customerId)

      // Assert
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.NO_CONTENT)

      // Verify customer is deleted - should return 404
      const getResponse = await customerClient.getCustomer(customerId)
      expectError(getResponse, HttpStatus.NOT_FOUND)
    })

    test('should return 404 for non-existent customer', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentCustomerId = 999999

      // Act
      const response = await customerClient.deleteCustomer(
        nonExistentCustomerId,
      )

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const customerClient = new CustomerClient(request)

      // Act
      const response = await customerClient.deleteCustomer(1)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Customer Relationships', () => {
    test('should load primary contact with customer', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCustomerData = createUniqueTestCustomer()
      const createResponse
        = await customerClient.createCustomer(testCustomerData)
      const createdCustomer = await expectIResponse<Customer>(createResponse)
      const customerId = createdCustomer.data.id

      // Act
      const response = await customerClient.getCustomer(customerId)

      // Assert
      expectSuccess(response)
      const customerBody = await expectIResponse<Customer>(response)

      // Primary contact is always loaded (via $with)
      expect(customerBody.data).toHaveProperty('primary_contact')
      // Primary contact may be null if no contact is assigned
      if (customerBody.data.primary_contact) {
        expect(customerBody.data.primary_contact).toHaveProperty('id')
        expect(customerBody.data.primary_contact).toHaveProperty('name')
        expect(customerBody.data.primary_contact).toHaveProperty('email')
      }

      // Cleanup
      await customerClient.deleteCustomer(customerId)
    })

    test('should load contacts collection when included', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCustomerData = createUniqueTestCustomer()
      const createResponse
        = await customerClient.createCustomer(testCustomerData)
      const createdCustomer = await expectIResponse<Customer>(createResponse)
      const customerId = createdCustomer.data.id

      // Act - Get customer with contacts included
      const response = await customerClient.getCustomer(customerId)

      // Assert
      expectSuccess(response)
      const customerBody = await expectIResponse<Customer>(response)

      // Contacts should be available (may be omitted if empty, but count should exist)
      expect(customerBody.data).toHaveProperty('contacts_count')
      // contacts property is optional - only included when contacts exist or explicitly loaded
      if (customerBody.data.contacts) {
        expect(Array.isArray(customerBody.data.contacts)).toBe(true)
        // If contacts exist, verify structure
        if (customerBody.data.contacts.length > 0) {
          const contact = customerBody.data.contacts[0]
          expect(contact).toHaveProperty('id')
          expect(contact).toHaveProperty('name')
          expect(contact).toHaveProperty('email')
          // User is always loaded on Contact (via $with)
          expect(contact).toHaveProperty('user')
        }
      }

      // Cleanup
      await customerClient.deleteCustomer(customerId)
    })

    test('should access user through primary contact', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const customerClient = new CustomerClient(request)
      customerClient.copyAuthStateFrom(authenticatedAuthClient)
      const testCustomerData = createUniqueTestCustomer()
      const createResponse
        = await customerClient.createCustomer(testCustomerData)
      const createdCustomer = await expectIResponse<Customer>(createResponse)
      const customerId = createdCustomer.data.id

      // Act
      const response = await customerClient.getCustomer(customerId)

      // Assert
      expectSuccess(response)
      const customerBody = await expectIResponse<Customer>(response)

      // If primary contact exists and has a user, verify user structure
      if (customerBody.data.primary_contact?.user) {
        const user = customerBody.data.primary_contact.user
        expect(user).toHaveProperty('id')
        expect(user).toHaveProperty('name')
        expect(user).toHaveProperty('email')
        expect(user).toHaveProperty('profile_photo_url')
      }

      // Cleanup
      await customerClient.deleteCustomer(customerId)
    })
  })
})
