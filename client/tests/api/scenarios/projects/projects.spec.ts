import type {
  CreateProjectRequest,
  PaginatedProjectsResponse,
  Project,
  UpdateProjectRequest,
} from '../../features/projects/project-types'

import { ProjectClient } from '../../features/projects/project-client'
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
 * Pure function: Create unique test project data
 * Uses timestamp to ensure uniqueness
 */
function createUniqueTestProject(baseName = 'Test Project'): CreateProjectRequest {
  const timestamp = Date.now()
  return {
    name: `${baseName} ${timestamp}`,
    description: `Test project description ${timestamp}`,
    status: 'active',
    category: 'development',
    start_date: new Date().toISOString().split('T')[0],
    end_date: null,
    progress: 0,
  }
}

/**
 * Pure function: Create update data
 */
function createUpdateData(updates: Partial<UpdateProjectRequest>): UpdateProjectRequest {
  return { ...updates }
}

test.describe('Projects API', { tag: ['@api', '@projects'] }, () => {
  test.describe('List Projects', () => {
    test('should get paginated list of projects', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act
      const response = await projectClient.getProjects(1, 15)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<PaginatedProjectsResponse>
      const projectsBody = await expectIResponse<PaginatedProjectsResponse>(response)

      // Assert - Pagination structure
      expect(projectsBody.data).toHaveProperty('data')
      expect(projectsBody.data).toHaveProperty('current_page')
      expect(projectsBody.data).toHaveProperty('per_page')
      expect(projectsBody.data).toHaveProperty('total')
      expect(projectsBody.data).toHaveProperty('last_page')
      expect(projectsBody.data).toHaveProperty('first_page_url')
      expect(projectsBody.data).toHaveProperty('last_page_url')
      expect(projectsBody.data).toHaveProperty('path')

      // Assert - Data should be an array
      expect(Array.isArray(projectsBody.data.data)).toBe(true)

      // Assert - If projects exist, verify structure
      if (projectsBody.data.data.length > 0) {
        const project = projectsBody.data.data[0]
        expect(project).toHaveProperty('id')
        expect(project).toHaveProperty('name')
        expect(project).toHaveProperty('status')
        expect(project).toHaveProperty('category')
        expect(project).toHaveProperty('created_at')
        expect(project).toHaveProperty('updated_at')
      }
    })

    test('should handle pagination parameters', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)

      // Act - Request page 1 with 5 items per page
      const response = await projectClient.getProjects(1, 5)

      // Assert
      expectSuccess(response)
      const projectsBody = await expectIResponse<PaginatedProjectsResponse>(response)
      expect(projectsBody.data.per_page).toBe(5)
      expect(projectsBody.data.current_page).toBe(1)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const projectClient = new ProjectClient(request)

      // Act
      const response = await projectClient.getProjects()

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Show Project', () => {
    test('should get project by id', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create a project first
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const testProjectData = createUniqueTestProject()
      const createResponse = await projectClient.createProject(testProjectData)
      const createdProject = await expectIResponse<Project>(createResponse)
      const projectId = createdProject.data.id

      // Act
      const response = await projectClient.getProject(projectId)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      // Assert - Response structure matches IResponse<Project>
      const projectBody = await expectIResponse<Project>(response)

      // Assert - Project data matches
      expect(projectBody.data.id).toBe(projectId)
      expect(projectBody.data).toHaveProperty('name')
      expect(projectBody.data).toHaveProperty('status')
      expect(projectBody.data).toHaveProperty('category')
      expect(projectBody.data).toHaveProperty('created_at')
      expect(projectBody.data).toHaveProperty('updated_at')

      // Cleanup
      await projectClient.deleteProject(projectId)
    })

    test('should return 404 for non-existent project', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentProjectId = 999999

      // Act
      const response = await projectClient.getProject(nonExistentProjectId)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const projectClient = new ProjectClient(request)

      // Act
      const response = await projectClient.getProject(1)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Create Project', () => {
    test('should create project successfully', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create unique test project data
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const testProjectData = createUniqueTestProject()

      // Act
      const response = await projectClient.createProject(testProjectData)

      // Assert - Response should succeed
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.CREATED)

      // Assert - Response structure matches IResponse<Project>
      const projectBody = await expectIResponse<Project>(response)

      // Assert - Project data matches input
      expect(projectBody.data).toHaveProperty('id')
      expect(projectBody.data.name).toBe(testProjectData.name)
      expect(projectBody.data.status).toBe(testProjectData.status)
      expect(projectBody.data.category).toBe(testProjectData.category)
      expect(projectBody.data).toHaveProperty('created_at')
      expect(projectBody.data).toHaveProperty('updated_at')

      // Cleanup - Delete created project
      await projectClient.deleteProject(projectBody.data.id)
    })

    test('should return validation errors for missing name', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateProjectRequest = {
        name: '',
        status: 'active',
        category: 'development',
      }

      // Act
      const response = await projectClient.createProject(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for invalid status', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateProjectRequest = {
        name: `Test Project ${Date.now()}`,
        status: 'invalid-status' as any,
        category: 'development',
      }

      // Act
      const response = await projectClient.createProject(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for invalid category', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateProjectRequest = {
        name: `Test Project ${Date.now()}`,
        status: 'active',
        category: 'invalid-category' as any,
      }

      // Act
      const response = await projectClient.createProject(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for invalid date range', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const today = new Date()
      const yesterday = new Date(today)
      yesterday.setDate(yesterday.getDate() - 1)
      const invalidData: CreateProjectRequest = {
        name: `Test Project ${Date.now()}`,
        status: 'active',
        category: 'development',
        start_date: today.toISOString().split('T')[0],
        end_date: yesterday.toISOString().split('T')[0], // End date before start date
      }

      // Act
      const response = await projectClient.createProject(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return validation errors for invalid progress', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const invalidData: CreateProjectRequest = {
        name: `Test Project ${Date.now()}`,
        status: 'active',
        category: 'development',
        progress: 150, // Progress > 100
      }

      // Act
      const response = await projectClient.createProject(invalidData)

      // Assert
      await expectValidationErrors(response)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      const testProjectData = createUniqueTestProject()

      // Act
      const response = await projectClient.createProject(testProjectData)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Update Project', () => {
    test('should update project successfully', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create a project first
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const testProjectData = createUniqueTestProject()
      const createResponse = await projectClient.createProject(testProjectData)
      const createdProject = await expectIResponse<Project>(createResponse)
      const projectId = createdProject.data.id

      // Prepare update data
      const updateData = createUpdateData({
        name: 'Updated Project Name',
      })

      // Act
      const response = await projectClient.updateProject(projectId, updateData)

      // Assert
      expectSuccess(response)
      expect(response.status()).toBe(HttpStatus.OK)

      const projectBody = await expectIResponse<Project>(response)
      expect(projectBody.data.name).toBe('Updated Project Name')
      expect(projectBody.data.status).toBe(testProjectData.status) // Status should remain unchanged

      // Cleanup
      await projectClient.deleteProject(projectId)
    })

    test('should update project status', async ({ request, authenticatedAuthClient }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const testProjectData = createUniqueTestProject()
      const createResponse = await projectClient.createProject(testProjectData)
      const createdProject = await expectIResponse<Project>(createResponse)
      const projectId = createdProject.data.id

      const updateData = createUpdateData({
        status: 'completed',
      })

      // Act
      const response = await projectClient.updateProject(projectId, updateData)

      // Assert
      expectSuccess(response)
      const projectBody = await expectIResponse<Project>(response)
      expect(projectBody.data.status).toBe('completed')

      // Cleanup
      await projectClient.deleteProject(projectId)
    })

    test('should return validation errors for invalid status on update', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const testProjectData = createUniqueTestProject()
      const createResponse = await projectClient.createProject(testProjectData)
      const createdProject = await expectIResponse<Project>(createResponse)
      const projectId = createdProject.data.id

      const updateData = createUpdateData({
        status: 'invalid-status' as any,
      })

      // Act
      const response = await projectClient.updateProject(projectId, updateData)

      // Assert
      await expectValidationErrors(response)

      // Cleanup
      await projectClient.deleteProject(projectId)
    })

    test('should return 404 for non-existent project on update', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentProjectId = 999999
      const updateData = createUpdateData({
        name: 'Updated Name',
      })

      // Act
      const response = await projectClient.updateProject(nonExistentProjectId, updateData)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      const updateData = createUpdateData({
        name: 'Updated Name',
      })

      // Act
      const response = await projectClient.updateProject(1, updateData)

      // Assert
      expectUnauthenticated(response)
    })
  })

  test.describe('Delete Project', () => {
    test('should delete project successfully', async ({ request, authenticatedAuthClient }) => {
      // Arrange - Create a project first
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const testProjectData = createUniqueTestProject()
      const createResponse = await projectClient.createProject(testProjectData)
      const createdProject = await expectIResponse<Project>(createResponse)
      const projectId = createdProject.data.id

      // Act
      const response = await projectClient.deleteProject(projectId)

      // Assert - Should return 204 No Content or 200 OK
      expect([HttpStatus.OK, HttpStatus.NO_CONTENT]).toContain(response.status())

      // Verify project is deleted
      const getResponse = await projectClient.getProject(projectId)
      expectError(getResponse, HttpStatus.NOT_FOUND)
    })

    test('should return 404 for non-existent project on delete', async ({
      request,
      authenticatedAuthClient,
    }) => {
      // Arrange
      const projectClient = new ProjectClient(request)
      projectClient.copyAuthStateFrom(authenticatedAuthClient)
      const nonExistentProjectId = 999999

      // Act
      const response = await projectClient.deleteProject(nonExistentProjectId)

      // Assert
      expectError(response, HttpStatus.NOT_FOUND)
    })

    test('should return 401 when not authenticated', async ({ request }) => {
      // Arrange
      const projectClient = new ProjectClient(request)

      // Act
      const response = await projectClient.deleteProject(1)

      // Assert
      expectUnauthenticated(response)
    })
  })
})
