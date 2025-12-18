import type { AxiosResponse } from 'axios'

/**
 * Download a Blob from an Axios response
 */
export function downloadBlobFromAxiosResponse(
  response: AxiosResponse<Blob>,
  fallbackFilename: string,
) {
  const blob = response.data
  const disposition = response.headers['content-disposition']

  const filename = disposition?.match(/filename="(.+)"/)?.[1] ?? fallbackFilename

  const url = URL.createObjectURL(blob)

  try {
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    link.rel = 'noopener'

    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } finally {
    URL.revokeObjectURL(url)
  }
}
