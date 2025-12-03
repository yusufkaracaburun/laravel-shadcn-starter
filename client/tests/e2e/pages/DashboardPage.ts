import type { Page } from '@playwright/test'

import { expect } from '@playwright/test'

import { BasePage } from './BasePage'

/**
 * Dashboard Page Object Model
 * Handles all interactions with the dashboard page
 */
export class DashboardPage extends BasePage {
  constructor(page: Page) {
    super(page)
  }

  /**
   * Navigate to dashboard page
   */
  async navigate(): Promise<void> {
    await this.navigateTo('/dashboard')
    await this.waitForLoadState()
  }

  /**
   * Click logout button
   * Note: This opens the user menu dropdown first, then clicks logout
   */
  async logout(): Promise<void> {
    // First, open the user menu
    const userMenu = this.getByTestId('navigation_user-menu')
    await userMenu.click()

    // Then click logout
    const logoutButton = this.getByTestId('dashboard_logout_button')
    await logoutButton.click()
  }

  /**
   * Verify we're on the dashboard page
   */
  async verifyOnPage(): Promise<void> {
    await expect(this.page).toHaveURL(/.*\/dashboard/)
    const dashboardPage = this.getByTestId('dashboard_page')
    await expect(dashboardPage).toBeVisible()
  }

  /**
   * Check if dashboard is visible
   */
  async isVisible(): Promise<boolean> {
    const dashboardPage = this.getByTestId('dashboard_page')
    return await dashboardPage.isVisible().catch(() => false)
  }
}
