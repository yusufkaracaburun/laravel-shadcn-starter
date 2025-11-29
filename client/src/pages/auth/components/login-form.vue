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
        <UiButton variant="link" class="px-0 text-muted-foreground" @click="$router.push('/auth/sign-up')">
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
            <UiInput id="email" v-model="email" type="email" placeholder="m@example.com" required :disabled="loading" />
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
            />
          </div>

          <UiButton type="submit" class="w-full" :disabled="loading">
            <UiSpinner v-if="loading" class="mr-2" />
            Login
          </UiButton>
        </div>
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
