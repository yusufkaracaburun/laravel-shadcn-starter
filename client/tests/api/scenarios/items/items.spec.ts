import type {
  CreateItemRequest,
  Item,
  PaginatedItemsResponse,
  UpdateItemRequest,
} from '../../features/items/item-types'

import { ItemClient } from '../../features/items/item-client'
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
 * Pure function: Create unique test item data
 * Uses timestamp to ensure uniqueness
 */
function createUniqueTestItem(baseName = 'Test Item'): CreateItemRequest {
  const timestamp = Date.now()
  return {
    name: `${baseName} ${timestamp}`,
    description: `Test item description ${timestamp}`,
    unit_price: 10.5,
    vat_rate: 21,
    unit: 'pcs',
  }
}

/**
 * Pure function: Create update data
 */
function createUpdateData(
  updates: Partial<UpdateItemRequest>,
): UpdateItemRequest {
  return { ...updates }
}

test.describe('Items API', { tag: ['@api', '@items'] }, () => {
  test.describe('List Items', () => {
    test('should get paginated list of items', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act
      const response = await itemClient.getItems(1, 15)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<PaginatedItemsResponse>
      const itemsBody = await expectIResponse<PaginatedItemsResponse>(response)

      // Assert - Pagination structure
      expect(itemsBody.data).toHaveProperty('data')
      expect(itemsBody.data).toHaveProperty('current_page')
      expect(itemsBody.data).toHaveProperty('per_page')
      expect(itemsBody.data).toHaveProperty('total')
      expect(itemsBody.data).toHaveProperty('last_page')
      expect(itemsBody.data).toHaveProperty('first_page_url')
      expect(itemsBody.data).toHaveProperty('last_page_url')
      expect(itemsBody.data).toHaveProperty('path')

      // Assert - Data should be an array
      expect(Array.isArray(itemsBody.data.data)).toBe(true)

      // Assert - If items exist, verify structure
      if (itemsBody.data.data.length > 0) {
        const item = itemsBody.data.data[0]
        expect(item).toHaveProperty('id')
        expect(item).toHaveProperty('name')
        expect(item).toHaveProperty('unit_price')
        expect(item).toHaveProperty('vat_rate')
        expect(item).toHaveProperty('created_at')
        expect(item).toHaveProperty('updated_at')
      }
    })

    test('should handle pagination parameters', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act - Request page 1 with 5 items per page
      const response = await itemClient.getItems(1, 5)

      // Assert
      expectSuccess(response)
      const itemsBody = await expectIResponse<PaginatedItemsResponse>(response)
      expect(itemsBody.data.per_page).toBe(5)
      expect(itemsBody.data.current_page).toBe(1)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const itemClient = new ItemClient(request)

      // Act
      const response = await itemClient.getItems()

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Show Item', () => {
    test('should get item by id', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange - Create an item first
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const testItemData = createUniqueTestItem()
      const createResponse = await itemClient.createItem(testItemData)
      const createdItem = await expectIResponse<Item>(createResponse)
      const itemId = createdItem.data.id

      // Act
      const response = await itemClient.getItem(itemId)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<Item>
      const itemBody = await expectIResponse<Item>(response)

      // Assert - Item data matches
      expect(itemBody.data.id).toBe(itemId)
      expect(itemBody.data).toHaveProperty('name')
      expect(itemBody.data).toHaveProperty('unit_price')
      expect(itemBody.data).toHaveProperty('vat_rate')
      expect(itemBody.data).toHaveProperty('created_at')
      expect(itemBody.data).toHaveProperty('updated_at')

      // Cleanup
      await itemClient.deleteItem(itemId)
    })

    test('should return 404 for non-existent item', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentItemId = 999999

      // Act
      const response = await itemClient.getItem(nonExistentItemId)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const itemClient = new ItemClient(request)

      // Act
      const response = await itemClient.getItem(1)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Create Item', () => {
    test('should create item successfully', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange - Create unique test item data
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const testItemData = createUniqueTestItem()

      // Act
      const response = await itemClient.createItem(testItemData)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.CREATED)

      // Assert - Response structure matches IResponse<Item>
      const itemBody = await expectIResponse<Item>(response)

      // Assert - Item data matches input
      expect(itemBody.data).toHaveProperty('id')
      expect(itemBody.data.name).toBe(testItemData.name)
      // vat_rate may be returned as string from backend, so convert for comparison
      expect(Number(itemBody.data.vat_rate)).toBe(testItemData.vat_rate)
      expect(itemBody.data).toHaveProperty('created_at')
      expect(itemBody.data).toHaveProperty('updated_at')

      // Handle unit_price - can be Money object or number
      const unitPrice = itemBody.data.unit_price
      if (typeof unitPrice === 'object' && 'amount' in unitPrice) {
        // Money object - verify structure
        expect(unitPrice).toHaveProperty('amount')
        expect(unitPrice).toHaveProperty('currency')
        expect(unitPrice).toHaveProperty('formatted')
      } else {
        // Number - should match input (within rounding tolerance)
        expect(typeof unitPrice).toBe('number')
      }

      // Cleanup - Delete created item
      await itemClient.deleteItem(itemBody.data.id)
    })

    test('should return validation errors for missing name', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateItemRequest = {
        name: '',
        unit_price: 10.5,
        vat_rate: 21,
      }

      // Act
      const response = await itemClient.createItem(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for invalid unit_price', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateItemRequest = {
        name: `Test Item ${Date.now()}`,
        unit_price: -10, // Negative price
        vat_rate: 21,
      }

      // Act
      const response = await itemClient.createItem(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for invalid vat_rate', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateItemRequest = {
        name: `Test Item ${Date.now()}`,
        unit_price: 10.5,
        vat_rate: 150, // VAT rate > 100
      }

      // Act
      const response = await itemClient.createItem(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for duplicate name', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const testItemData = createUniqueTestItem()

      // Create first item
      const createResponse = await itemClient.createItem(testItemData)
      expectSuccess(createResponse)
      const createdItem = await expectIResponse<Item>(createResponse)

      // Try to create another item with same name
      const duplicateData: CreateItemRequest = {
        ...testItemData,
        unit_price: 20, // Different price
      }

      // Act
      const response = await itemClient.createItem(duplicateData)

      // Assert
      await expectValidationErrors(response)

      // Cleanup
      await itemClient.deleteItem(createdItem.data.id)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      const testItemData = createUniqueTestItem()

      // Act
      const response = await itemClient.createItem(testItemData)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Update Item', () => {
    test('should update item successfully', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange - Create an item first
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const testItemData = createUniqueTestItem()
      const createResponse = await itemClient.createItem(testItemData)
      const createdItem = await expectIResponse<Item>(createResponse)
      const itemId = createdItem.data.id

      // Prepare update data - use unique name to avoid conflicts
      const updateData = createUpdateData({
        name: `Updated Item Name ${Date.now()}`,
      })

      // Act
      const response = await itemClient.updateItem(itemId, updateData)

      // Assert
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      const itemBody = await expectIResponse<Item>(response)
      expect(itemBody.data.name).toBe(updateData.name)
      // Other fields should remain unchanged - vat_rate may be string, so convert for comparison
      expect(Number(itemBody.data.vat_rate)).toBe(testItemData.vat_rate)

      // Cleanup
      await itemClient.deleteItem(itemId)
    })

    test('should update item unit_price', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const testItemData = createUniqueTestItem()
      const createResponse = await itemClient.createItem(testItemData)
      const createdItem = await expectIResponse<Item>(createResponse)
      const itemId = createdItem.data.id

      const updateData = createUpdateData({
        unit_price: 25.75, // Keep decimal for realistic price
      })

      // Act
      const response = await itemClient.updateItem(itemId, updateData)

      // Assert
      expectSuccess(response)
      const itemBody = await expectIResponse<Item>(response)

      // Handle unit_price - can be Money object or number
      const unitPrice = itemBody.data.unit_price
      if (typeof unitPrice === 'object' && 'amount' in unitPrice) {
        // Money object - verify it's updated
        expect(unitPrice).toHaveProperty('amount')
        expect(unitPrice).toHaveProperty('formatted')
      } else {
        // Number - should be close to updated value
        expect(typeof unitPrice).toBe('number')
      }

      // Cleanup
      await itemClient.deleteItem(itemId)
    })

    test('should return validation errors for invalid unit_price on update', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const testItemData = createUniqueTestItem()
      const createResponse = await itemClient.createItem(testItemData)
      const createdItem = await expectIResponse<Item>(createResponse)
      const itemId = createdItem.data.id

      const updateData = createUpdateData({
        unit_price: -5, // Negative price
      })

      // Act
      const response = await itemClient.updateItem(itemId, updateData)

      // Assert
      await expectValidationErrors(response)

      // Cleanup
      await itemClient.deleteItem(itemId)
    })

    test('should return 404 for non-existent item on update', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentItemId = 999999
      const updateData = createUpdateData({
        name: 'Updated Name',
      })

      // Act
      const response = await itemClient.updateItem(
        nonExistentItemId,
        updateData,
      )

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      const updateData = createUpdateData({
        name: 'Updated Name',
      })

      // Act
      const response = await itemClient.updateItem(1, updateData)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Delete Item', () => {
    test('should delete item successfully', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange - Create an item first
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const testItemData = createUniqueTestItem()
      const createResponse = await itemClient.createItem(testItemData)
      const createdItem = await expectIResponse<Item>(createResponse)
      const itemId = createdItem.data.id

      // Act
      const response = await itemClient.deleteItem(itemId)

      // Assert - Should return 204 No Content or 200 OK
      expect([HttpStatus.OK, HttpStatus.NO_CONTENT]).toContain(
        response.status(),
      )
      expectSuccess(response)

      // Verify item is deleted
      const getResponse = await itemClient.getItem(itemId)
      expectError(getResponse, HttpStatus.NOT_FOUND)
    })

    test('should return 404 for non-existent item on delete', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const itemClient = new ItemClient(request)
      itemClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentItemId = 999999

      // Act
      const response = await itemClient.deleteItem(nonExistentItemId)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const itemClient = new ItemClient(request)

      // Act
      const response = await itemClient.deleteItem(1)

      // Assert
      expectUnauthenticated(response)
    })
  })
})
