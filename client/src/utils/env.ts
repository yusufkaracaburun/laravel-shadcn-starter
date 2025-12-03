import { h } from 'vue'
import { toast } from 'vue-sonner'
import { z } from 'zod'

/**
 * Load .env file and validate it against the schema
 * Has this file, it will be loaded automatically by vite and we will be have environment variables available type
 * If EnvSchema Object has Key but not in .env file, it will be have a error in page
 */

const EnvSchema = z.object({
  // Add your environment variables here, for example:
  VITE_APP_ENV: z.string(),
  VITE_SERVER_API_URL: z.url(),
  VITE_SERVER_API_PREFIX: z.string(),
  VITE_SERVER_API_TIMEOUT: z.coerce.number().default(5000),
  // Reverb (WebSocket) configuration
  VITE_REVERB_ENABLED: z.string().default('false'),
  VITE_REVERB_APP_KEY: z.string().optional(),
  VITE_REVERB_HOST: z.string().optional(),
  VITE_REVERB_PORT: z.coerce.number().default(9999),
  VITE_REVERB_SCHEME: z.enum(['http', 'https']).default('http'),
})

export type env = z.infer<typeof EnvSchema>

// eslint-disable-next-line ts/no-redeclare
const { data: env, error } = EnvSchema.safeParse(import.meta.env)

if (error) {
  console.error('❌ Invalid env')
  console.error(error.issues)
  toast.error(`Env error: you should check your .env file`, {
    description: h(
      'pre',
      { class: 'mt-2 w-[340px] rounded-md bg-slate-950 p-4' },
      h('code', { class: 'text-white' }, JSON.stringify(error.issues, null, 2)),
    ),
  })
}

export default env!
