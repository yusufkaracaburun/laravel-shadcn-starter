/**
 * Composable for Reverb notifications
 *
 * This composable provides utilities for working with Reverb notifications.
 * The global listener is automatically set up in the app plugin.
 *
 * @example
 * ```ts
 * // The global listener is already set up, just broadcast from Laravel:
 * // event(new NotificationCreated(userId: 1, message: 'Hello!', type: 'success'))
 * ```
 */
export function useReverbNotifications() {
  return {
    /**
     * Note: Notifications are handled automatically by the global listener.
     * Just broadcast events from Laravel and they will appear as toast notifications.
     *
     * For custom listeners, use useEcho() directly.
     */
  }
}
