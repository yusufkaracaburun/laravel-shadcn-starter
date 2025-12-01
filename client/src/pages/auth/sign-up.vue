<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import { useForm, Field as VeeField } from 'vee-validate'
import { z } from 'zod'

import {
  Field,
  FieldError,
  FieldGroup,
  FieldLabel,
} from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { useAuth } from '@/composables/use-auth'

import AuthTitle from './components/auth-title.vue'
import GitHubButton from './components/github-button.vue'
import GoogleButton from './components/google-button.vue'
import PrivacyPolicyButton from './components/privacy-policy-button.vue'
import TermsOfServiceButton from './components/terms-of-service-button.vue'

const { register, loading } = useAuth()

const registerSchema = toTypedSchema(
  z.object({
    name: z.string().min(1, 'Name is required.').min(2, 'Name must be at least 2 characters.'),
    email: z.string().email('Please enter a valid email address.').min(1, 'Email is required.'),
    password: z.string().min(8, 'Password must be at least 8 characters.'),
    password_confirmation: z.string().min(1, 'Please confirm your password.'),
  }).refine(data => data.password === data.password_confirmation, {
    message: 'Passwords do not match.',
    path: ['password_confirmation'],
  }),
)

const { handleSubmit, setFieldError } = useForm({
  validationSchema: registerSchema,
  initialValues: {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  },
})

const onSubmit = handleSubmit(async (values) => {
  try {
    await register({
      name: values.name,
      email: values.email,
      password: values.password,
      password_confirmation: values.password_confirmation,
    })
  }
  catch (error: any) {
    // Handle backend validation errors (422)
    if (error.response?.status === 422) {
      const backendErrors = error.response.data.errors || {}
      // Set field errors from backend response
      Object.keys(backendErrors).forEach((field) => {
        const fieldErrors = backendErrors[field]
        if (Array.isArray(fieldErrors) && fieldErrors.length > 0) {
          // Map backend field names to form field names
          const formField = field === 'password_confirmation' ? 'password_confirmation' : field
          setFieldError(formField as 'name' | 'email' | 'password' | 'password_confirmation', fieldErrors[0])
        }
      })
    }
    // Other errors are handled in useAuth composable (shows toast)
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
          <form id="register-form" @submit="onSubmit">
            <FieldGroup>
              <VeeField v-slot="{ field, errors }" name="name">
                <Field :data-invalid="!!errors.length">
                  <FieldLabel for="register-form-name">
                    Name
                  </FieldLabel>
                  <Input
                    id="register-form-name"
                    v-bind="field"
                    placeholder="John Doe"
                    :aria-invalid="!!errors.length"
                  />
                  <FieldError v-if="errors.length" :errors="errors" />
                </Field>
              </VeeField>

              <VeeField v-slot="{ field, errors }" name="email">
                <Field :data-invalid="!!errors.length">
                  <FieldLabel for="register-form-email">
                    Email
                  </FieldLabel>
                  <Input
                    id="register-form-email"
                    v-bind="field"
                    type="email"
                    placeholder="m@example.com"
                    autocomplete="email"
                    :aria-invalid="!!errors.length"
                  />
                  <FieldError v-if="errors.length" :errors="errors" />
                </Field>
              </VeeField>

              <VeeField v-slot="{ field, errors }" name="password">
                <Field :data-invalid="!!errors.length">
                  <FieldLabel for="register-form-password">
                    Password
                  </FieldLabel>
                  <Input
                    id="register-form-password"
                    v-bind="field"
                    type="password"
                    placeholder="******"
                    autocomplete="new-password"
                    :aria-invalid="!!errors.length"
                  />
                  <FieldError v-if="errors.length" :errors="errors" />
                </Field>
              </VeeField>

              <VeeField v-slot="{ field, errors }" name="password_confirmation">
                <Field :data-invalid="!!errors.length">
                  <FieldLabel for="register-form-password-confirmation">
                    Confirm Password
                  </FieldLabel>
                  <Input
                    id="register-form-password-confirmation"
                    v-bind="field"
                    type="password"
                    placeholder="******"
                    autocomplete="new-password"
                    :aria-invalid="!!errors.length"
                  />
                  <FieldError v-if="errors.length" :errors="errors" />
                </Field>
              </VeeField>
            </FieldGroup>
          </form>

          <UiButton type="submit" form="register-form" class="w-full" :disabled="loading">
            <UiSpinner v-if="loading" class="mr-2" />
            Create Account
          </UiButton>

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
