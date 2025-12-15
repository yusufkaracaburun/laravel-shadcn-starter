import type { APIRequestContext } from '@playwright/test'

import { AuthClient } from '../../auth/auth-client'
import { UserClient } from '../../users/user-client'
import { ProjectClient } from '../../projects/project-client'
import { ItemClient } from '../../items/item-client'
import { CustomerClient } from '../../customers/customer-client'

/**
 * Facade class providing unified interface to all API clients
 * Aggregates all feature clients for convenient access
 */
export class ApiClient {
  public readonly auth: AuthClient
  public readonly users: UserClient
  public readonly projects: ProjectClient
  public readonly items: ItemClient
  public readonly customers: CustomerClient

  constructor(request: APIRequestContext) {
    this.auth = new AuthClient(request)
    this.users = new UserClient(request)
    this.projects = new ProjectClient(request)
    this.items = new ItemClient(request)
    this.customers = new CustomerClient(request)
  }
}

