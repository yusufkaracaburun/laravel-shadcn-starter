<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm, Field as VeeField } from 'vee-validate'
import { z } from 'zod'

import { Field, FieldError, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { useAuth } from '@/composables/use-auth.composable'

import GitHubButton from './github-button.vue'
import GoogleButton from './google-button.vue'
import PrivacyPolicyButton from './privacy-policy-button.vue'
import TermsOfServiceButton from './terms-of-service-button.vue'
import ToForgotPasswordLink from './to-forgot-password-link.vue'

const { login, loading } = useAuth()

const loginSchema = toTypedSchema(
  z.object({
    email: z
      .string()
      .email('Please enter a valid email address.')
      .min(1, 'Email is required.'),
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
  <UiCard class="w-full max-w-sm">
    <UiCardHeader>
      <UiCardTitle class="text-2xl">
        Login
      </UiCardTitle>
      <UiCardDescription>
        Enter your email and password below to log into your account. Not have
        an account?
        <UiButton
          variant="link"
          class="px-0 text-muted-foreground"
          @click="$router.push('/auth/sign-up')"
        >
          Sign Up
        </UiButton>
      </UiCardDescription>
    </UiCardHeader>
    <UiCardContent>
      <form id="login-form" @submit="onSubmit">
        <div class="grid gap-4">
          <div class="grid gap-2">
            <VeeField v-slot="{ field, errors }" name="email">
              <Field :data-invalid="!!errors.length">
                <FieldLabel for="login-form-email">
                  Email
                </FieldLabel>
                <Input
                  id="login-form-email"
                  v-bind="field"
                  type="email"
                  placeholder="m@example.com"
                  autocomplete="email"
                  :aria-invalid="!!errors.length"
                />
                <FieldError v-if="errors.length" :errors="errors" />
              </Field>
            </VeeField>
          </div>

          <div class="grid gap-2">
            <VeeField v-slot="{ field, errors }" name="password">
              <Field :data-invalid="!!errors.length">
                <div class="flex items-center justify-between">
                  <FieldLabel for="login-form-password">
                    Password
                  </FieldLabel>
                  <ToForgotPasswordLink />
                </div>
                <Input
                  id="login-form-password"
                  v-bind="field"
                  type="password"
                  placeholder="*********"
                  autocomplete="current-password"
                  :aria-invalid="!!errors.length"
                />
                <FieldError v-if="errors.length" :errors="errors" />
              </Field>
            </VeeField>
          </div>

          <UiButton
            type="submit"
            form="login-form"
            class="w-full"
            :disabled="loading"
          >
            <UiSpinner v-if="loading" class="mr-2" />
            Login
          </UiButton>

          <UiSeparator label="Or continue with" />

          <div class="flex flex-col items-center justify-between gap-4">
            <GitHubButton />
            <GoogleButton />
          </div>

          <UiCardDescription>
            By clicking login, you agree to our
            <TermsOfServiceButton />
            and
            <PrivacyPolicyButton />
          </UiCardDescription>
        </div>
      </form>
    </UiCardContent>
  </UiCard>
</template>
