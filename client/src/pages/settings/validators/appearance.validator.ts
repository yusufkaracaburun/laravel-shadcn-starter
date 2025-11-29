import { z } from 'zod'

export const appearanceValidator = z.object({
  theme: z
    .enum(['light', 'dark'], {
      errorMap: () => ({ message: 'Please select a theme.' }),
    }),
  font: z
    .enum(['inter', 'manrope', 'system'], {
      errorMap: () => ({ message: 'Please select a font.' }),
    }),
})
