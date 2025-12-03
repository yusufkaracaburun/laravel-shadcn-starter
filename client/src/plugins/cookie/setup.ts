/**
 * Cookie plugin using CookieStore API
 * Reference: https://developer.mozilla.org/en-US/docs/Web/API/CookieStore
 */

/**
 * Check if CookieStore API is available
 */
export function isCookieStoreAvailable(): boolean {
  if (typeof window === 'undefined') return false
  return 'cookieStore' in window
}

/**
 * Get a single cookie by name
 */
export async function getCookie(name: string): Promise<CookieListItem | null> {
  if (!isCookieStoreAvailable()) {
    console.warn('CookieStore API is not available')
    return null
  }

  try {
    const cookie = await cookieStore.get(name)
    return cookie || null
  } catch (error) {
    console.error(`Error getting cookie "${name}":`, error)
    return null
  }
}

/**
 * Get all cookies or cookies matching a name
 */
export async function getAllCookies(name?: string): Promise<CookieList> {
  if (!isCookieStoreAvailable()) {
    console.warn('CookieStore API is not available')
    return []
  }

  try {
    const cookies = await cookieStore.getAll(name)
    return cookies
  } catch (error) {
    console.error('Error getting all cookies:', error)
    return []
  }
}

/**
 * Set a cookie
 */
export async function setCookie(name: string, value: string): Promise<boolean>
export async function setCookie(options: CookieStoreSetOptions): Promise<boolean>
export async function setCookie(
  nameOrOptions: string | CookieStoreSetOptions,
  value?: string,
): Promise<boolean> {
  if (!isCookieStoreAvailable()) {
    console.warn('CookieStore API is not available')
    return false
  }

  try {
    if (typeof nameOrOptions === 'string' && value !== undefined) {
      // Set with name and value
      await cookieStore.set(nameOrOptions, value)
    } else if (typeof nameOrOptions === 'object') {
      // Set with options object
      await cookieStore.set(nameOrOptions)
    } else {
      throw new TypeError('Invalid arguments for setCookie()')
    }
    return true
  } catch (error) {
    console.error('Error setting cookie:', error)
    return false
  }
}

/**
 * Delete a cookie by name or options
 */
export async function removeCookie(name: string): Promise<boolean>
export async function removeCookie(options: CookieStoreDeleteOptions): Promise<boolean>
export async function removeCookie(
  nameOrOptions: string | CookieStoreDeleteOptions,
): Promise<boolean> {
  if (!isCookieStoreAvailable()) {
    console.warn('CookieStore API is not available')
    return false
  }

  try {
    await cookieStore.delete(nameOrOptions)
    return true
  } catch (error) {
    console.error('Error deleting cookie:', error)
    return false
  }
}

/**
 * Watch for cookie changes
 */
export function watchCookieChanges(callback: (event: Event) => void): () => void {
  if (!isCookieStoreAvailable()) {
    console.warn('CookieStore API is not available')
    return () => {}
  }

  cookieStore.addEventListener('change', callback)

  // Return cleanup function
  return () => {
    cookieStore.removeEventListener('change', callback)
  }
}

/**
 * Setup cookie plugin
 * This function can be used to initialize the cookie plugin if needed
 */
export function setupCookie() {
  // Plugin initialization if needed in the future
  // Currently, all functions are static and don't require setup
}

export function getCookieValue(name: string): string | null {
  const cookie = document.cookie.split('; ').find((row) => row.startsWith(`${name}=`))
  if (!cookie) return null
  return decodeURIComponent(cookie.split('=')[1])
}