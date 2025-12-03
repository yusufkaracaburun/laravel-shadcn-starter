import type { Locator, Page } from '@playwright/test'

/**
 * Base Page Object Model with common functionality
 * All page objects should extend this class
 */
export class BasePage {
  constructor(protected page: Page) {}

  /**
   * Navigate to a specific URL
   */
  async navigateTo(url: string): Promise<void> {
    await this.page.goto(url)
  }

  /**
   * Get element by test ID (preferred method)
   * Falls back to getByRole if test ID not found
   */
  protected getByTestId(testId: string): Locator {
    return this.page.getByTestId(testId)
  }

  /**
   * Get element by role (fallback method)
   */
  protected getByRole(
    role: 'button' | 'link' | 'textbox' | 'heading',
    options?: { name?: string },
  ): Locator {
    return this.page.getByRole(role, options)
  }

  /**
   * Wait for page to load
   */
  async waitForLoadState(
    state: 'load' | 'domcontentloaded' | 'networkidle' = 'networkidle',
  ): Promise<void> {
    await this.page.waitForLoadState(state)
  }

  /**
   * Get current URL
   */
  getCurrentUrl(): string {
    return this.page.url()
  }

  /**
   * Wait for URL to match pattern
   */
  async waitForURL(url: string | RegExp, options?: { timeout?: number }): Promise<void> {
    await this.page.waitForURL(url, options)
  }
}
