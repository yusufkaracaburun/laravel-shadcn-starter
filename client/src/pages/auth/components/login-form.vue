<script lang="ts" setup>
import { useAuth } from '@/composables/use-auth'

import GitHubButton from './github-button.vue'
import GoogleButton from './google-button.vue'
import PrivacyPolicyButton from './privacy-policy-button.vue'
import TermsOfServiceButton from './terms-of-service-button.vue'
import ToForgotPasswordLink from './to-forgot-password-link.vue'

const { login, loading } = useAuth()

const email = ref('')
const password = ref('')
const remember = ref(false)

async function handleLogin() {
  if (!email.value || !password.value) {
    return
  }

  await login({
    email: email.value,
    password: password.value,
    remember: remember.value,
  })
}
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
        <UiButton variant="link" class="px-0 text-muted-foreground" @click="$router.push('/auth/sign-up')" data-testid="login-form_sign-up_link">
          Sign Up
        </UiButton>
      </UiCardDescription>
    </UiCardHeader>
    <UiCardContent class="grid gap-4">
      <form @submit.prevent="handleLogin">
        <div class="grid gap-4">
          <div class="grid gap-2">
            <UiLabel for="email">
              Email
            </UiLabel>
            <UiInput id="email" v-model="email" type="email" placeholder="m@example.com" required :disabled="loading" data-testid="login-form_email_input" />
          </div>
          <div class="grid gap-2">
            <div class="flex items-center justify-between">
              <UiLabel for="password">
                Password
              </UiLabel>
              <ToForgotPasswordLink />
            </div>
            <UiInput
              id="password" v-model="password" type="password" required placeholder="*********"
              :disabled="loading"
              data-testid="login-form_password_input"
            />
          </div>

          <UiButton type="submit" class="w-full" :disabled="loading" data-testid="login-form_submit_button">
            <UiSpinner v-if="loading" class="mr-2" data-testid="login-form_loading_spinner" />
            Login
          </UiButton>
        </div>
      </form>

      <UiSeparator label="Or continue with" />

      <div class="flex flex-col items-center justify-between gap-4">
        <GitHubButton :test-id="'login-form_github_button'" />
        <GoogleButton :test-id="'login-form_google_button'" />
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
