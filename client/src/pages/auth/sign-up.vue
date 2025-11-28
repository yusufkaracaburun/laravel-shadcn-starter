<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import type { AxiosError } from 'axios'
import { toast } from 'vue-sonner'
import { z } from 'zod'

import { useAxios } from '@/composables/use-axios'
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import env from '@/utils/env'

import AuthTitle from './components/auth-title.vue'
import GitHubButton from './components/github-button.vue'
import GoogleButton from './components/google-button.vue'
import PrivacyPolicyButton from './components/privacy-policy-button.vue'
import TermsOfServiceButton from './components/terms-of-service-button.vue'

const registerSchema = toTypedSchema(z.object({
  firstName: z.string().min(1, 'First name is required'),
  lastName: z.string().min(1, 'Last name is required'),
  email: z.string().email('Please enter a valid email address'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
  password_confirmation: z.string().min(1, 'Please confirm your password'),
}).refine((data) => data.password === data.password_confirmation, {
  message: 'Passwords do not match',
  path: ['password_confirmation'],
}))

const { handleSubmit } = useForm({
  validationSchema: registerSchema,
})

const router = useRouter()
const { axiosInstance } = useAxios()
const loading = ref(false)
const error = ref<string | null>(null)

const onSubmit = handleSubmit(async (values) => {
  loading.value = true
  error.value = null

  try {
    // Fortify routes are at root level, not under /api
    const baseURL = env.VITE_SERVER_API_URL
    const response = await axiosInstance.post('/register', {
      name: `${values.firstName} ${values.lastName}`,
      email: values.email,
      password: values.password,
      password_confirmation: values.password_confirmation,
    }, {
      baseURL,
    })

    if (response.status === 201) {
      toast.success('Account created successfully!')
      router.push({ path: '/auth/sign-in' })
    }
  }
  catch (err) {
    const axiosError = err as AxiosError<{ message?: string; errors?: Record<string, string[]> }>
    if (axiosError.response?.status === 422) {
      const errorData = axiosError.response.data
      if (errorData?.errors) {
        const firstError = Object.values(errorData.errors)[0]
        error.value = Array.isArray(firstError) ? firstError[0] : 'Validation failed'
      }
      else if (errorData?.message) {
        error.value = errorData.message
      }
      else {
        error.value = 'Validation failed'
      }
    }
    else {
      error.value = 'An error occurred during registration. Please try again.'
    }
    toast.error(error.value)
  }
  finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="flex items-center justify-center min-h-screen p-4 min-w-screen">
    <main class="flex flex-col gap-4">
      <AuthTitle />
      <UiCard class="max-w-sm mx-auto">
        <UiCardHeader>
          <UiCardTitle class="text-xl">
            Sign Up
          </UiCardTitle>
          <UiCardDescription>
            Enter your email and password to create an account.
            Already have an account?
            <UiButton variant="link" class="px-0 text-muted-foreground" @click="$router.push('/auth/sign-in')">
              Sign In
            </UiButton>
          </UiCardDescription>
        </UiCardHeader>
        <UiCardContent>
          <form @submit="onSubmit" class="grid gap-4">
            <div class="grid grid-cols-2 gap-4">
              <FormField v-slot="{ componentField }" name="firstName">
                <FormItem>
                  <FormLabel>First name</FormLabel>
                  <FormControl>
                    <Input id="first-name" placeholder="Max" v-bind="componentField" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>

              <FormField v-slot="{ componentField }" name="lastName">
                <FormItem>
                  <FormLabel>Last name</FormLabel>
                  <FormControl>
                    <Input id="last-name" placeholder="Robinson" v-bind="componentField" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>
            </div>

            <FormField v-slot="{ componentField }" name="email">
              <FormItem>
                <FormLabel>Email</FormLabel>
                <FormControl>
                  <Input id="email" type="email" placeholder="m@example.com" v-bind="componentField" />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="password">
              <FormItem>
                <FormLabel>Password</FormLabel>
                <FormControl>
                  <Input id="password" type="password" placeholder="******" v-bind="componentField" />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="password_confirmation">
              <FormItem>
                <FormLabel>Confirm Password</FormLabel>
                <FormControl>
                  <Input id="password-confirmation" type="password" placeholder="******" v-bind="componentField" />
                </FormControl>
                <FormMessage />
              </FormItem>
            </FormField>

            <div v-if="error" class="text-sm text-destructive" role="alert">
              {{ error }}
            </div>

            <UiButton type="submit" class="w-full" :disabled="loading">
              <UiSpinner v-if="loading" class="mr-2" />
              Create Account
            </UiButton>
          </form>

          <UiSeparator label="Or continue with" />

          <div class="flex flex-col items-center justify-between gap-4">
            <GitHubButton />
            <GoogleButton />
          </div>

          <UiCardDescription>
            By creating an account, you agree to our
            <TermsOfServiceButton />
            and
            <PrivacyPolicyButton />
          </UiCardDescription>
        </UiCardContent>
      </UiCard>
    </main>
  </div>
</template>
