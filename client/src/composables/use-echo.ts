import type Echo from 'laravel-echo'

import { getEcho } from '@/plugins/echo/setup'

/**
 * Composable for accessing Laravel Echo instance
 * Use this to subscribe to channels and listen to events
 *
 * @example
 * ```ts
 * const echo = useEcho()
 * if (echo) {
 *   echo.channel('notifications')
 *     .listen('NotificationCreated', (e) => {
 *       console.log('New notification:', e)
 *     })
 * }
 * ```
 */
export function useEcho(): Echo | null {
  return getEcho()
}
