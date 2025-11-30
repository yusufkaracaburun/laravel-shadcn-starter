<script setup lang="ts">
import { useAuth } from '@/composables/use-auth'

import AuthTitle from './components/auth-title.vue'
import GitHubButton from './components/github-button.vue'
import GoogleButton from './components/google-button.vue'
import PrivacyPolicyButton from './components/privacy-policy-button.vue'
import TermsOfServiceButton from './components/terms-of-service-button.vue'

const { register, loading } = useAuth()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const terms = ref(false)

async function handleRegister() {
  if (!name.value || !email.value || !password.value || !passwordConfirmation.value) {
    return
  }

  if (password.value !== passwordConfirmation.value) {
    return
  }

  await register({
    name: name.value,
    email: email.value,
    password: password.value,
    password_confirmation: passwordConfirmation.value,
    terms: terms.value,
  })
}
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
            <UiButton
              variant="link" class="px-0 text-muted-foreground"
              @click="$router.push('/auth/sign-in')"
              data-testid="register-form_sign-in_link"
            >
              Sign In
            </UiButton>
          </UiCardDescription>
        </UiCardHeader>
        <UiCardContent>
          <form @submit.prevent="handleRegister">
            <div class="grid gap-4">
              <div class="grid gap-2">
                <UiLabel for="name">
                  Name
                </UiLabel>
                <UiInput
                  id="name"
                  v-model="name"
                  placeholder="John Doe"
                  required
                  :disabled="loading"
                  data-testid="register-form_name_input"
                />
              </div>
              <div class="grid gap-2">
                <UiLabel for="email">
                  Email
                </UiLabel>
                <UiInput
                  id="email"
                  v-model="email"
                  type="email"
                  placeholder="m@example.com"
                  required
                  :disabled="loading"
                  data-testid="register-form_email_input"
                />
              </div>
              <div class="grid gap-2">
                <UiLabel for="password">
                  Password
                </UiLabel>
                <UiInput
                  id="password"
                  v-model="password"
                  type="password"
                  placeholder="******"
                  required
                  :disabled="loading"
                  data-testid="register-form_password_input"
                />
              </div>
              <div class="grid gap-2">
                <UiLabel for="password-confirmation">
                  Confirm Password
                </UiLabel>
                <UiInput
                  id="password-confirmation"
                  v-model="passwordConfirmation"
                  type="password"
                  placeholder="******"
                  required
                  :disabled="loading"
                  data-testid="register-form_password-confirmation_input"
                />
              </div>
              <UiButton type="submit" class="w-full" :disabled="loading" data-testid="register-form_submit_button">
                <UiSpinner v-if="loading" class="mr-2" data-testid="register-form_loading_spinner" />
                Create Account
              </UiButton>
            </div>
          </form>

          <UiSeparator label="Or continue with" />

          <div class="flex flex-col items-center justify-between gap-4">
            <GitHubButton :test-id="'register-form_github_button'" />
            <GoogleButton :test-id="'register-form_google_button'" />
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
