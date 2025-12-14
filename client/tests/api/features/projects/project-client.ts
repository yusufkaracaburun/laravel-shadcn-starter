import type { APIResponse } from '@playwright/test'

import type { CreateProjectRequest, UpdateProjectRequest, Project, PaginatedProjectsResponse } from './project-types'
import type { IResponse } from '../shared/types'
import { BaseClient } from '../shared/core/base-client'

export class ProjectClient extends BaseClient {
  /**
   * Get paginated list of projects
   * @param page - Page number (default: 1)
   * @param perPage - Number of items per page (default: 15)
   * Returns raw APIResponse
   */
  async getProjects(page: number = 1, perPage: number = 15): Promise<APIResponse> {
    const endpoint = `/api/project?page=${page}&per_page=${perPage}`
    return this.get(endpoint)
  }

  /**
   * Get paginated list of projects with typed response
   * @param page - Page number (default: 1)
   * @param perPage - Number of items per page (default: 15)
   * Returns IResponse<PaginatedProjectsResponse>
   */
  async getProjectsTyped(page: number = 1, perPage: number = 15): Promise<IResponse<PaginatedProjectsResponse>> {
    const response = await this.getProjects(page, perPage)
    return response.json() as Promise<IResponse<PaginatedProjectsResponse>>
  }

  /**
   * Get a specific project by ID
   * @param projectId - Project ID
   * Returns raw APIResponse
   */
  async getProject(projectId: number): Promise<APIResponse> {
    const endpoint = `/api/project/${projectId}`
    return this.get(endpoint)
  }

  /**
   * Get a specific project by ID with typed response
   * @param projectId - Project ID
   * Returns IResponse<Project>
   */
  async getProjectTyped(projectId: number): Promise<IResponse<Project>> {
    const response = await this.getProject(projectId)
    return response.json() as Promise<IResponse<Project>>
  }

  /**
   * Create a new project
   * @param data - Project creation data
   * Returns raw APIResponse
   */
  async createProject(data: CreateProjectRequest): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    return this.post('/api/project', data)
  }

  /**
   * Create a new project with typed response
   * @param data - Project creation data
   * Returns IResponse<Project>
   */
  async createProjectTyped(data: CreateProjectRequest): Promise<IResponse<Project>> {
    const response = await this.createProject(data)
    return response.json() as Promise<IResponse<Project>>
  }

  /**
   * Update an existing project
   * @param projectId - Project ID
   * @param data - Project update data
   * Returns raw APIResponse
   */
  async updateProject(projectId: number, data: UpdateProjectRequest): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    const endpoint = `/api/project/${projectId}`
    return this.put(endpoint, data)
  }

  /**
   * Update an existing project with typed response
   * @param projectId - Project ID
   * @param data - Project update data
   * Returns IResponse<Project>
   */
  async updateProjectTyped(projectId: number, data: UpdateProjectRequest): Promise<IResponse<Project>> {
    const response = await this.updateProject(projectId, data)
    return response.json() as Promise<IResponse<Project>>
  }

  /**
   * Delete a project
   * @param projectId - Project ID
   * Returns raw APIResponse
   */
  async deleteProject(projectId: number): Promise<APIResponse> {
    await this.ensureCsrfCookie()
    const endpoint = `/api/project/${projectId}`
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

