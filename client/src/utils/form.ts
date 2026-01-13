export function mapObjectDeep<T extends object>(
  obj: T,
  mapper: (value: any) => any,
): T {
  if (obj === null || typeof obj !== 'object') {
    return mapper(obj)
  }

  if (Array.isArray(obj)) {
    return obj.map((item) => mapObjectDeep(item, mapper)) as T
  }

  return Object.fromEntries(
    Object.entries(obj).map(([key, value]) => [
      key,
      mapObjectDeep(value, mapper),
    ]),
  ) as T
}

/**
 * Converts an object to FormData if it contains File instances
 * @param data - The data object to convert
 * @param options - Options for FormData conversion
 * @param [options.excludeId] - Whether to exclude the 'id' field from FormData
 * @returns FormData if files are present, otherwise returns the original data
 */
export function convertToFormData<T extends Record<string, any>>(
  data: T,
  options?: {
    excludeId?: boolean
  },
): T | FormData {
  // Check if any value is a File
  const hasFile = Object.values(data).some((value) => value instanceof File)

  if (!hasFile) {
    return data
  }

  const formData = new FormData()
  Object.entries(data).forEach(([key, value]) => {
    // Skip id if excludeId is true
    if (key === 'id' && options?.excludeId) {
      return
    }

    if (value instanceof File) {
      formData.append(key, value)
    } else if (value !== null && value !== undefined) {
      formData.append(key, String(value))
    }
  })

  return formData
}

/**
 * Sets form field errors from backend validation errors (422)
 * @param error - The error object from axios
 * @param setFieldError - The vee-validate setFieldError function
 * @param validFields - Array of valid field names for type safety
 */
export function setFormFieldErrors<T extends readonly string[]>(
  error: any,
  setFieldError: (field: T[number], message: string) => void,
  validFields: T,
): void {
  if (error.response?.status !== 422) {
    return
  }

  const backendErrors = error.response.data.errors || {}
  Object.entries(backendErrors).forEach(([field, fieldErrors]) => {
    if (
      Array.isArray(fieldErrors) &&
      fieldErrors.length > 0 &&
      validFields.includes(field as T[number])
    ) {
      setFieldError(field as T[number], fieldErrors[0])
    }
  })
}
