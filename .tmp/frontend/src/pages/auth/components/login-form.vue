<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'

import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form'
import { useAuth } from '@/composables/use-auth'

import GitHubButton from './github-button.vue'
import GoogleButton from './google-button.vue'
import PrivacyPolicyButton from './privacy-policy-button.vue'
import TermsOfServiceButton from './terms-of-service-button.vue'
import ToForgotPasswordLink from './to-forgot-password-link.vue'

const { login, loading, error } = useAuth()

const loginSchema = toTypedSchema(z.object({
  email: z.email('Invalid email address').default('test@example.com'),
  password: z.string().min(1, 'Password is required').default('password'),
}))

const { handleSubmit } = useForm({
  validationSchema: loginSchema,
})

const onSubmit = handleSubmit((values) => {
  login(values)
})
</script>

<template>
  <UiCard class="w-full max-w-sm">
    <UiCardHeader>
      <UiCardTitle class="text-2xl">
        Login
      </UiCardTitle>
      <UiCardDescription>
        Enter your email and password below to log into your account.
        Not have an account?
        <UiButton
          variant="link" class="px-0 text-muted-foreground"
          @click="$router.push('/auth/sign-up')"
        >
          Sign Up
        </UiButton>
      </UiCardDescription>
    </UiCardHeader>
    <UiCardContent class="grid gap-4">
      <form class="grid gap-4" @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="email">
          <FormItem>
            <FormLabel data-testid="login-form_email_label">
              Email
            </FormLabel>
            <FormControl>
              <UiInput
                id="email"
                type="email"
                placeholder="m@example.com"
                data-testid="login-form_email_input"
                v-bind="componentField"
              />
            </FormControl>
            <FormMessage data-testid="login-form_email_error" />
          </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="password">
          <FormItem>
            <div class="flex items-center justify-between">
              <FormLabel data-testid="login-form_password_label">
                Password
              </FormLabel>
              <ToForgotPasswordLink />
            </div>
            <FormControl>
              <UiInput
                id="password"
                type="password"
                placeholder="*********"
                data-testid="login-form_password_input"
                v-bind="componentField"
              />
            </FormControl>
            <FormMessage data-testid="login-form_password_error" />
          </FormItem>
        </FormField>

        <div v-if="error" class="text-sm text-destructive" data-testid="login-form_error_message">
          {{ error }}
        </div>

        <UiButton type="submit" class="w-full" :disabled="loading" data-testid="login-form_login_button">
          <UiSpinner v-if="loading" class="mr-2" />
          {{ loading ? 'Logging in...' : 'Login' }}
        </UiButton>
      </form>

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
    </UiCardContent>
  </UiCard>
</template>
