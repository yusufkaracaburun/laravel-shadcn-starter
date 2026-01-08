import type { App } from 'vue'

import { storeToRefs } from 'pinia'
import { nextTick, watch } from 'vue'

import { useEcho } from '@/composables/use-echo'
import { useToast } from '@/composables/use-toast'
import { useAuthStore } from '@/stores/auth.store'

import pinia from '../pinia/setup'

interface NotificationEvent {
  message: string
  type?: 'success' | 'error' | 'info' | 'warning'
  title?: string
  description?: string
  data?: Record<string, any>
}

/**
 * Setup global Reverb event listeners for toast notifications
 * This plugin automatically listens to:
 * - User-specific private channels (when authenticated)
 * - Public notification channels
 */
export function setupReverbListener(app: App) {
  // Wait for next tick to ensure Pinia is fully initialized
  nextTick(() => {
    const echo = useEcho()
    if (!echo) {
      console.warn(
        '⚠️ Echo is not available. Reverb listener will not be initialized.',
      )
      console.warn(
        'Make sure Reverb is enabled and configured in your .env file',
      )
      return
    }

    console.warn('🔌 Echo is available, setting up Reverb listeners...')

    const { showInfo, showSuccess, showError, showWarning } = useToast()

    // Test toast to verify toast system is working
    showInfo('Reverb listener initialized', {
      duration: 3000,
    })

    const authStore = useAuthStore(pinia)
    const { user } = storeToRefs(authStore)

    let userChannel: any = null
    let publicChannel: any = null
    let exampleChannel: any = null

    // Listen to example channel for debugging
    exampleChannel = echo.channel('example-channel')
    exampleChannel
      .listen('.example.event', (data: any) => {
        console.warn('📢 Example event received:', data)
        showInfo('Example Event', {
          description: data.message || JSON.stringify(data, null, 2),
        })
      })
      .error((error: any) => {
        console.error('❌ Example channel error:', error)
      })

    console.warn('✅ Subscribed to example-channel')

    // Listen to public notifications channel
    publicChannel = echo.channel('notifications')
    publicChannel
      .listen('.notification.created', (data: NotificationEvent) => {
        console.warn('📢 Public notification received:', data)
        handleNotification(data)
      })
      .error((error: any) => {
        console.error('❌ Public channel error:', error)
      })

    console.warn('✅ Subscribed to public notifications channel')

    // Watch for user authentication state changes
    watch(
      user,
      (currentUser, previousUser) => {
        // Clean up previous user channel
        if (userChannel && previousUser?.id) {
          echo.leave(`user.${previousUser.id}`)
          userChannel = null
        }

        // Subscribe to user-specific channel if authenticated
        if (currentUser?.id) {
          console.warn(
            `✅ Subscribing to private channel: user.${currentUser.id}`,
          )
          userChannel = echo.private(`user.${currentUser.id}`)
          userChannel
            .listen('.notification.created', (data: NotificationEvent) => {
              console.warn('📢 Private notification received:', data)
              handleNotification(data)
            })
            .error((error: any) => {
              console.error('❌ Private channel error:', error)
            })
        } else {
          console.warn(
            '⚠️ User not authenticated, skipping private channel subscription',
          )
        }
      },
      { immediate: true },
    )

    // Handle notification and show toast
    function handleNotification(data: NotificationEvent) {
      console.warn('🔔 handleNotification called with data:', data)

      const {
        message,
        type = 'info',
        title,
        description,
        data: eventData,
      } = data

      const notificationTitle = title || message || 'Notification'
      const notificationDescription =
        description ||
        (eventData && Object.keys(eventData).length > 0
          ? JSON.stringify(eventData, null, 2)
          : undefined)

      console.warn('🍞 Showing toast:', {
        notificationTitle,
        type,
        notificationDescription,
      })

      try {
        // Ensure we have a valid title
        if (!notificationTitle || notificationTitle.trim() === '') {
          console.error('❌ Cannot show toast: notificationTitle is empty')
          return
        }

        const toastOptions: any = {}
        // Only add description if it has a value
        if (notificationDescription) {
          toastOptions.description = notificationDescription
        }

        let toastResult: any = null
        switch (type) {
          case 'success':
            toastResult = showSuccess(notificationTitle, toastOptions)
            break
          case 'error':
            toastResult = showError(notificationTitle, toastOptions)
            break
          case 'warning':
            toastResult = showWarning(notificationTitle, toastOptions)
            break
          case 'info':
          default:
            toastResult = showInfo(notificationTitle, toastOptions)
            break
        }

        if (toastResult) {
          console.warn('✅ Toast called successfully, ID:', toastResult)
        } else {
          console.error(
            '❌ Toast returned null/undefined - toast may not have rendered',
          )
        }
      } catch (error) {
        console.error('❌ Toast error:', error)
      }
    }

    // Cleanup on app unmount
    app.config.globalProperties.$reverbListener = {
      cleanup: () => {
        if (userChannel && authStore.user?.id) {
          echo.leave(`user.${authStore.user.id}`)
          userChannel = null
        }
        if (publicChannel) {
          echo.leave('notifications')
          publicChannel = null
        }
        if (exampleChannel) {
          echo.leave('example-channel')
          exampleChannel = null
        }
      },
    }
  })
}
