import type { AxiosError } from 'axios'

import axios from 'axios'
import { defineStore } from 'pinia'

/**
 * Error types supported by the error store
 */
export type ErrorType = 'api' | 'validation' | 'network' | 'general' | null

/**
 * Error state structure
 */
export interface ErrorState {
  type: ErrorType
  status?: number
  message: string
  validationErrors?: Record<string, string[]>
  originalError?: unknown
  timestamp?: number
  context?: string
}

/**
 * Options for setting errors
 */
export interface SetErrorOptions {
  context?: string
  preservePrevious?: boolean
}

/**
 * Extended options for validation errors
 */
interface ValidationErrorOptions extends SetErrorOptions {
  status?: number
  originalError?: unknown
}

/**
 * API response data structure
 */
interface ApiResponseData {
  message?: string
  error?: string
  errors?: Record<string, string[]>
  [key: string]: unknown
}

/**
 * HTTP status code to default message mapping
 */
const STATUS_CODE_MESSAGES: Record<number, string> = {
  400: 'Bad request',
  401: 'Unauthorized. Please log in again.',
  403: 'You are not authorized to perform this action',
  404: 'Resource not found',
  422: 'Validation error',
  429: 'Too many requests. Please try again later.',
  500: 'Internal server error',
  503: 'Service unavailable. Please try again later.',
} as const

/**
 * Network error patterns
 */
const NETWORK_ERROR_PATTERNS = ['Network', 'timeout', 'ECONNREFUSED'] as const

/**
 * Extract error message from API response data
 */
function extractApiErrorMessage(responseData: ApiResponseData | undefined, axiosError: AxiosError): string {
  if (responseData?.message) {
    return responseData.message
  }
  if (responseData?.error) {
    return responseData.error
  }
  if (axiosError.message) {
    return axiosError.message
  }
  return 'An error occurred'
}

/**
 * Get default message for HTTP status code
 */
function getStatusMessage(status: number | undefined, axiosError: AxiosError): string {
  if (!status) {
    // Handle network/timeout errors without status
    if (axiosError.code === 'ECONNABORTED' || axiosError.message.includes('timeout')) {
      return 'Request timeout. Please try again.'
    }
    if (axiosError.code === 'ERR_NETWORK' || !axiosError.response) {
      return 'Network error. Please check your connection.'
    }
    return 'An error occurred'
  }

  // Check status code mapping
  if (status in STATUS_CODE_MESSAGES) {
    return STATUS_CODE_MESSAGES[status]
  }

  // Handle status code ranges
  if (status >= 400 && status < 500) {
    return 'Client error occurred'
  }
  if (status >= 500) {
    return 'Server error occurred'
  }

  return 'An error occurred'
}

/**
 * Check if error is a network error based on message patterns
 */
function isNetworkError(error: Error): boolean {
  return NETWORK_ERROR_PATTERNS.some(pattern => error.message.includes(pattern))
}

/**
 * Extract network error message
 */
function extractNetworkErrorMessage(error: Error | AxiosError): string {
  if (error instanceof Error && !axios.isAxiosError(error)) {
    if (error.message.includes('timeout')) {
      return 'Request timeout. Please try again.'
    }
    if (error.message.includes('ECONNREFUSED')) {
      return 'Connection refused. The server may be unavailable.'
    }
    return error.message || 'Network error. Please check your connection.'
  }

  if (axios.isAxiosError(error)) {
    if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
      return 'Request timeout. Please try again.'
    }
    if (error.code === 'ERR_NETWORK') {
      return 'Network error. Please check your connection.'
    }
  }

  return 'Network error. Please check your connection.'
}

/**
 * Extract first validation error message
 */
function extractFirstValidationError(errors: Record<string, string[]>): string {
  const firstErrorKey = Object.keys(errors)[0]
  return errors[firstErrorKey]?.[0] ?? 'Validation failed'
}

/**
 * Extract validation errors from 422 responses
 */
function getValidationErrors(error: unknown): Record<string, string[]> {
  if (axios.isAxiosError(error)) {
    const responseData = error.response?.data as ApiResponseData | undefined
    if (error.response?.status === 422 && responseData?.errors) {
      return responseData.errors
    }
  }

  return {}
}

/**
 * Extract user-friendly error message from any error type
 */
function getErrorMessage(error: unknown): string {
  if (axios.isAxiosError(error)) {
    const responseData = error.response?.data as ApiResponseData | undefined
    return extractApiErrorMessage(responseData, error)
  }

  if (error instanceof Error) {
    return error.message
  }

  if (typeof error === 'string') {
    return error
  }

  return 'An unexpected error occurred'
}

/**
 * Error store for centralized error handling
 * Handles errors from API calls, composables, and services
 */
export const useErrorStore = defineStore('error', () => {
  const currentError = ref<ErrorState | null>(null)

  /**
   * Computed properties for easy access to error state
   */
  const hasError = computed(() => currentError.value !== null)
  const errorType = computed(() => currentError.value?.type ?? null)
  const errorStatus = computed(() => currentError.value?.status)
  const errorMessage = computed(() => currentError.value?.message ?? '')
  const validationErrors = computed(() => currentError.value?.validationErrors ?? {})
  const originalError = computed(() => currentError.value?.originalError)

  /**
   * Create error state object
   */
  function createErrorState(
    type: ErrorType,
    message: string,
    options?: SetErrorOptions & { status?: number, validationErrors?: Record<string, string[]>, originalError?: unknown },
  ): ErrorState {
    return {
      type,
      status: options?.status,
      message,
      validationErrors: options?.validationErrors,
      originalError: options?.originalError,
      timestamp: Date.now(),
      context: options?.context,
    }
  }

  /**
   * Clear the current error
   */
  function clearError() {
    currentError.value = null
  }

  /**
   * Set error from various sources
   * Automatically detects error type and parses accordingly
   */
  function setError(error: unknown, options?: SetErrorOptions) {
    if (!options?.preservePrevious) {
      clearError()
    }

    // Handle AxiosError
    if (axios.isAxiosError(error)) {
      setApiError(error, options)
      return
    }

    // Handle Error objects
    if (error instanceof Error) {
      if (isNetworkError(error)) {
        setNetworkError(error, options)
        return
      }
      setGeneralError(error.message, options)
      return
    }

    // Handle string errors
    if (typeof error === 'string') {
      setGeneralError(error, options)
      return
    }

    // Fallback for unknown error types
    setGeneralError('An unexpected error occurred', options)
  }

  /**
   * Set API error from AxiosError
   */
  function setApiError(axiosError: AxiosError, options?: SetErrorOptions) {
    const status = axiosError.response?.status
    const responseData = axiosError.response?.data as ApiResponseData | undefined

    // Handle validation errors (422)
    if (status === 422 && responseData?.errors) {
      setValidationError(responseData.errors, {
        ...options,
        status,
        originalError: axiosError,
      })
      return
    }

    // Extract error message
    const extractedMessage = extractApiErrorMessage(responseData, axiosError)
    const message = responseData?.message || responseData?.error
      ? extractedMessage
      : getStatusMessage(status, axiosError)

    currentError.value = createErrorState('api', message, {
      ...options,
      status,
      originalError: axiosError,
    })
  }

  /**
   * Set validation errors from 422 responses
   */
  function setValidationError(errors: Record<string, string[]>, options?: ValidationErrorOptions) {
    const message = extractFirstValidationError(errors)

    currentError.value = createErrorState('validation', message, {
      ...options,
      status: options?.status ?? 422,
      validationErrors: errors,
    })
  }

  /**
   * Set network/connection errors
   */
  function setNetworkError(error: Error | AxiosError, options?: SetErrorOptions) {
    const message = extractNetworkErrorMessage(error)

    currentError.value = createErrorState('network', message, {
      ...options,
      originalError: error,
    })
  }

  /**
   * Set general application errors
   */
  function setGeneralError(message: string, options?: SetErrorOptions) {
    currentError.value = createErrorState('general', message, options)
  }

  return {
    // State
    currentError: readonly(currentError),
    hasError,
    errorType,
    errorStatus,
    errorMessage,
    validationErrors,
    originalError,

    // Methods
    setError,
    setApiError,
    setValidationError,
    setNetworkError,
    setGeneralError,
    clearError,
    getErrorMessage,
    getValidationErrors,
  }
})
