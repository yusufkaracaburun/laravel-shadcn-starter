export interface HandleFileUploadOptions {
  /** Field name to set in the form */
  fieldName: string
  /** Function to set field value in the form */
  setFieldValue: (field: any, value: File | null) => void
  /** Callback to set preview (for images/files) */
  onPreview?: (preview: string | null) => void
  /** Maximum file size in bytes (default: 2MB) */
  maxSize?: number
  /** Allowed MIME types (e.g., ['image/*'] or ['image/jpeg', 'image/png']) */
  allowedTypes?: string[]
  /** Error message handler (e.g., toast.showError) */
  onError?: (message: string) => void
  /** Custom validation function */
  validate?: (file: File) => string | null
}

/**
 * Handles file upload from input change event
 * Validates file type and size, sets form field value, and creates preview
 * @param event - Input change event
 * @param options - Configuration options
 * @returns Promise that resolves with the file if valid, null otherwise
 */
export function handleFileUpload(
  event: Event,
  options: HandleFileUploadOptions,
): Promise<File | null> {
  return new Promise((resolve) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]

    // No file selected - clear field and preview
    if (!file) {
      options.setFieldValue(options.fieldName, null)
      options.onPreview?.(null)
      resolve(null)
      return
    }

    // Validate file type
    const allowedTypes = options.allowedTypes || ['image/*']
    const isTypeAllowed = allowedTypes.some((type) => {
      if (type.endsWith('/*')) {
        return file.type.startsWith(type.slice(0, -2))
      }
      return file.type === type
    })

    if (!isTypeAllowed) {
      const errorMessage = options.allowedTypes?.[0]?.startsWith('image/')
        ? 'Please select an image file.'
        : 'Invalid file type.'
      options.onError?.(errorMessage)
      target.value = ''
      resolve(null)
      return
    }

    // Validate file size
    const maxSize = options.maxSize || 2 * 1024 * 1024 // Default 2MB
    if (file.size > maxSize) {
      const sizeMB = (maxSize / (1024 * 1024)).toFixed(0)
      options.onError?.(`File size must be less than ${sizeMB}MB.`)
      target.value = ''
      resolve(null)
      return
    }

    // Custom validation
    if (options.validate) {
      const customError = options.validate(file)
      if (customError) {
        options.onError?.(customError)
        target.value = ''
        resolve(null)
        return
      }
    }

    // Set field value
    options.setFieldValue(options.fieldName, file)

    // Create preview if callback provided and file is an image
    if (options.onPreview && file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => {
        const preview = e.target?.result as string
        options.onPreview?.(preview)
      }
      reader.onerror = () => {
        options.onError?.('Failed to read file.')
        options.onPreview?.(null)
      }
      reader.readAsDataURL(file)
    }

    resolve(file)
  })
}
