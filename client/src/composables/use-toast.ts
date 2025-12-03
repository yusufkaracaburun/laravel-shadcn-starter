import type { App, Component } from 'vue'
import type { ExternalToast } from 'vue-sonner'

import { toast } from 'vue-sonner'

/**
 * Promise toast data type
 * Includes all ExternalToast options plus promise-specific fields
 */
type PromiseToastData<T = any> = Omit<ExternalToast, 'description'> & {
  loading?: string | Component
  success?: string | Component | ((data: T) => string | Component | Promise<string | Component>)
  error?: string | Component | ((error: any) => string | Component | Promise<string | Component>)
  description?: string | Component | ((data: T) => string | Component | Promise<string | Component>)
  finally?: () => void | Promise<void>
}

/**
 * Default toast configuration
 * These defaults will be used throughout the application
 */
export const defaultToastOptions: ExternalToast = {
  position: 'bottom-right',
  duration: 5000,
  closeButton: true,
  testId: 'toast',
}

/**
 * Toast configuration for different notification types
 */
export const toastConfig = {
  normal: {
    ...defaultToastOptions,
    duration: 5000,
  },
  action: {
    ...defaultToastOptions,
    duration: 5000,
    action: {
      label: 'Undo',
      onClick: () => console.warn('Undo'),
    },
  },
  success: {
    ...defaultToastOptions,
    duration: 4000,
  },
  error: {
    ...defaultToastOptions,
    duration: 6000, // Errors stay longer
  },
  warning: {
    ...defaultToastOptions,
    duration: 5000,
  },
  info: {
    ...defaultToastOptions,
    duration: 4000,
  },
  loading: {
    ...defaultToastOptions,
    duration: Infinity, // Loading toasts don't auto-dismiss
  },
  default: {
    ...defaultToastOptions,
    duration: 5000,
  },
} as const

/**
 * Default Toaster component props
 */
export const defaultToasterProps = {
  position: 'bottom-right' as const,
  theme: 'system' as const,
  richColors: true,
  expand: true,
  closeButton: true,
}

/**
 * Get default toast options
 * Use this when you want to merge with custom options
 */
export function getDefaultToastOptions(): ExternalToast {
  return { ...defaultToastOptions }
}

/**
 * Get toast config for a specific type
 */
export function getToastConfig(
  type: 'normal' | 'action' | 'success' | 'error' | 'warning' | 'info' | 'loading' | 'default',
): ExternalToast {
  return { ...toastConfig[type] }
}

/**
 * Get success toast options
 */
export function getSuccessToastOptions(): ExternalToast {
  return { ...toastConfig.success }
}

/**
 * Get error toast options
 */
export function getErrorToastOptions(): ExternalToast {
  return { ...toastConfig.error }
}

/**
 * Get warning toast options
 */
export function getWarningToastOptions(): ExternalToast {
  return { ...toastConfig.warning }
}

/**
 * Get info toast options
 */
export function getInfoToastOptions(): ExternalToast {
  return { ...toastConfig.info }
}

/**
 * Get normal toast options
 */
export function getNormalToastOptions(): ExternalToast {
  return { ...toastConfig.normal }
}

/**
 * Get action toast options
 */
export function getActionToastOptions(): ExternalToast {
  return { ...toastConfig.action }
}

/**
 * Get loading toast options
 */
export function getLoadingToastOptions(): ExternalToast {
  return { ...toastConfig.loading }
}

/**
 * Get default toast options (alias for normal)
 */
export function getDefaultTypeToastOptions(): ExternalToast {
  return { ...toastConfig.default }
}

/**
 * Setup vue-sonner plugin
 * This plugin centralizes toast configuration for the entire application
 */
export function setupVueSonner(app: App) {
  // Make default options available globally
  app.config.globalProperties.$toastDefaults = defaultToastOptions
  app.config.globalProperties.$toastConfig = toastConfig
}

/**
 * Composable for using toast notifications with default configuration
 *
 * @example
 * ```ts
 * const { showToast, showSuccess, showError, showWarning, showInfo } = useToast()
 *
 * // Use with defaults
 * showSuccess('Operation completed!')
 *
 * // Override defaults
 * showError('Something went wrong', { duration: 10000 })
 * ```
 */
export function useToast() {
  /**
   * Show a success toast
   */
  function showSuccess(message: string, options?: ExternalToast) {
    return toast.success(message, {
      ...getSuccessToastOptions(),
      ...options,
    })
  }

  /**
   * Show an error toast
   */
  function showError(message: string, options?: ExternalToast) {
    return toast.error(message, {
      ...getErrorToastOptions(),
      ...options,
    })
  }

  /**
   * Show a warning toast
   */
  function showWarning(message: string, options?: ExternalToast) {
    return toast.warning(message, {
      ...getWarningToastOptions(),
      ...options,
    })
  }

  /**
   * Show an info toast
   */
  function showInfo(message: string, options?: ExternalToast) {
    return toast.info(message, {
      ...getInfoToastOptions(),
      ...options,
    })
  }

  /**
   * Show a normal toast
   */
  function showNormal(message: string, options?: ExternalToast) {
    return toast.message(message, {
      ...getNormalToastOptions(),
      ...options,
    })
  }

  /**
   * Show an action toast (with action button)
   */
  function showAction(message: string, options?: ExternalToast) {
    return toast.message(message, {
      ...getActionToastOptions(),
      ...options,
    })
  }

  /**
   * Show a generic toast with default options (alias for normal)
   */
  function showToast(message: string, options?: ExternalToast) {
    return toast(message, {
      ...getDefaultToastOptions(),
      ...options,
    })
  }

  /**
   * Show a loading toast (useful for async operations)
   */
  function showLoading(message: string, options?: ExternalToast) {
    return toast.loading(message, {
      ...getLoadingToastOptions(),
      ...options,
    })
  }

  /**
   * Show a promise toast (automatically handles loading/success/error states)
   * Merges all options with default values
   *
   * @example
   * ```ts
   * showPromise(
   *   fetchUser(),
   *   {
   *     loading: 'Loading user...',
   *     success: (user) => `Welcome ${user.name}!`,
   *     error: 'Failed to load user',
   *     duration: 5000,
   *     position: 'top-right'
   *   }
   * )
   * ```
   */
  function showPromise<T>(promise: Promise<T>, data: PromiseToastData<T>) {
    const defaultOptions = getDefaultToastOptions()
    return toast.promise(promise, {
      ...defaultOptions,
      ...data,
    })
  }

  return {
    toast,
    showToast,
    showNormal,
    showAction,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showLoading,
    showPromise,
    getDefaultOptions: getDefaultToastOptions,
    getConfig: getToastConfig,
    getNormalOptions: getNormalToastOptions,
    getActionOptions: getActionToastOptions,
    getSuccessOptions: getSuccessToastOptions,
    getErrorOptions: getErrorToastOptions,
    getWarningOptions: getWarningToastOptions,
    getInfoOptions: getInfoToastOptions,
    getLoadingOptions: getLoadingToastOptions,
    getDefaultTypeOptions: getDefaultTypeToastOptions,
  }
}
