import { z } from 'zod'

export const appearanceValidator = z.object({
  theme: z.enum(['light', 'dark'], {
    message: 'Please select a theme.',
  }),
  font: z.enum(['inter', 'manrope', 'system'], {
    message: 'Please select a font.',
  }),
})
