<script setup lang="ts">
import type { HTMLAttributes } from 'vue'

import { toTypedSchema } from '@vee-validate/zod'
import { useForm, Field as VeeField } from 'vee-validate'
import { z } from 'zod'

import {
  Field,
  FieldDescription,
  FieldError,
  FieldGroup,
  FieldLabel,
  FieldSeparator,
} from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { useAuth } from '@/composables/use-auth'
import { cn } from '@/lib/utils'

import GitHubButton from './github-button.vue'
import GoogleButton from './google-button.vue'
import ToForgotPasswordLink from './to-forgot-password-link.vue'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const { login, loading } = useAuth()

const loginSchema = toTypedSchema(
  z.object({
    email: z.string().email('Please enter a valid email address.').min(1, 'Email is required.'),
    password: z.string().min(1, 'Password is required.'),
  }),
)

const { handleSubmit, setFieldError } = useForm({
  validationSchema: loginSchema,
  initialValues: {
    email: '',
    password: '',
  },
})

const onSubmit = handleSubmit(async (values) => {
  try {
    await login({
      email: values.email,
      password: values.password,
    })
  } catch (error: any) {
    // Handle backend validation errors (422)
    if (error.response?.status === 422) {
      const backendErrors = error.response.data.errors || {}
      // Set field errors from backend response
      Object.keys(backendErrors).forEach((field) => {
        const fieldErrors = backendErrors[field]
        if (Array.isArray(fieldErrors) && fieldErrors.length > 0) {
          setFieldError(field as 'email' | 'password', fieldErrors[0])
        }
      })
    }
    // 401 errors are handled in useAuth composable (shows toast)
  }
})
</script>

<template>
  <form id="login-form-2" :class="cn('flex flex-col gap-6', props.class)" @submit="onSubmit">
    <FieldGroup>
      <div class="flex flex-col items-center gap-1 text-center">
        <h1 class="text-2xl font-bold">Login to your account</h1>
        <p class="text-muted-foreground text-sm text-balance">
          Enter your email below to login to your account
        </p>
      </div>
      <Field>
        <VeeField v-slot="{ field, errors }" name="email">
          <FieldLabel for="login-form-2-email"> Email </FieldLabel>
          <Input
            id="login-form-2-email"
            v-bind="field"
            type="email"
            placeholder="m@example.com"
            autocomplete="email"
            :aria-invalid="!!errors.length"
          />
          <FieldError v-if="errors.length" :errors="errors" />
        </VeeField>
      </Field>
      <Field>
        <VeeField v-slot="{ field, errors }" name="password">
          <div class="flex items-center justify-between">
            <FieldLabel for="login-form-2-password"> Password </FieldLabel>
            <ToForgotPasswordLink />
          </div>
          <Input
            id="login-form-2-password"
            v-bind="field"
            type="password"
            placeholder="*********"
            autocomplete="current-password"
            :aria-invalid="!!errors.length"
          />
          <FieldError v-if="errors.length" :errors="errors" />
        </VeeField>
      </Field>
      <Field>
        <UiButton type="submit" form="login-form-2" class="w-full" :disabled="loading">
          <UiSpinner v-if="loading" class="mr-2" />
          Login
        </UiButton>
      </Field>
      <FieldSeparator>Or continue with</FieldSeparator>
      <Field>
        <div class="flex flex-col gap-4">
          <GitHubButton />
          <GoogleButton />
        </div>
        <FieldDescription class="text-center">
          Don't have an account?
          <UiButton
            variant="link"
            class="px-0 text-muted-foreground"
            @click="$router.push('/auth/sign-up')"
          >
            Sign up
          </UiButton>
        </FieldDescription>
      </Field>
    </FieldGroup>
  </form>
</template>
