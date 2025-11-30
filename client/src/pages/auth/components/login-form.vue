<script lang="ts" setup>
import { useAuth } from '@/composables/use-auth'
import env from '@/utils/env'

import { testusers } from '../../../../tests/.data/users.data'
import GitHubButton from './github-button.vue'
import GoogleButton from './google-button.vue'
import PrivacyPolicyButton from './privacy-policy-button.vue'
import TermsOfServiceButton from './terms-of-service-button.vue'
import ToForgotPasswordLink from './to-forgot-password-link.vue'

const { login, loading } = useAuth()

const email = ref('')
const password = ref('')
const remember = ref(false)

const isLocalEnv = computed(() => env.VITE_APP_ENV === 'local')

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

async function quickLogin(userKey: keyof typeof testusers) {
  const user = testusers[userKey]
  if (!user) {
    return
  }

  email.value = user.email
  password.value = user.password

  await login({
    email: user.email,
    password: user.password,
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
        <UiButton variant="link" class="px-0 text-muted-foreground" data-testid="login-form_sign-up_link" @click="$router.push('/auth/sign-up')">
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

      <template v-if="isLocalEnv">
        <UiSeparator label="Quick Login (Local Only)" />
        <div class="grid gap-2">
          <UiButton
            v-for="(user, key) in testusers"
            :key="key"
            variant="outline"
            size="sm"
            class="w-full justify-start"
            :disabled="loading"
            :data-testid="`login-form_quick-login_${key}_button`"
            @click="quickLogin(key as keyof typeof testusers)"
          >
            {{ user.name }}
          </UiButton>
        </div>
      </template>

      <UiCardDescription>
        By clicking login, you agree to our
        <TermsOfServiceButton />
        and
        <PrivacyPolicyButton />
      </UiCardDescription>
    </UiCardContent>
  </UiCard>
</template>
