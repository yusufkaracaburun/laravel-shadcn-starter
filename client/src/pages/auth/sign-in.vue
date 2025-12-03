<script setup lang="ts">
import { useAuth } from '@/composables/use-auth'
import env from '@/utils/env'

import { testusers } from '../../../tests/.data/users.data'
import AuthTitle from './components/auth-title.vue'
import LoginForm from './components/login-form.vue'

const { login, loading } = useAuth()

const isLocal = computed(() => env.VITE_APP_ENV === 'local')

async function quickLogin(userKey: keyof typeof testusers) {
  const user = testusers[userKey]
  try {
    await login({
      email: user.email,
      password: user.password,
    })
  } catch (error) {
    // Error handling is done in useAuth composable
    console.error('Quick login failed:', error)
  }
}
</script>

<template>
  <div class="flex items-center justify-center min-h-screen p-4 min-w-screen">
    <main class="flex flex-col gap-4">
      <AuthTitle />
      <LoginForm />

      <!-- Quick Login Buttons (Local Development Only) -->
      <UiCard v-if="isLocal" class="w-full max-w-sm">
        <UiCardHeader>
          <UiCardTitle class="text-lg"> Quick Login (Local Only) </UiCardTitle>
          <UiCardDescription> Click a button to quickly login with a test user. </UiCardDescription>
        </UiCardHeader>
        <UiCardContent>
          <div class="grid gap-2">
            <UiButton
              v-for="(user, key) in testusers"
              :key="key"
              variant="outline"
              class="w-full justify-start"
              :disabled="loading"
              data-testid="sign-in_quick-login-button"
              @click="quickLogin(key as keyof typeof testusers)"
            >
              <UiSpinner v-if="loading" class="mr-2" />
              <span class="font-medium">{{ user.name }}</span>
              <span class="ml-auto text-xs text-muted-foreground">{{ user.email }}</span>
            </UiButton>
          </div>
        </UiCardContent>
      </UiCard>
    </main>
  </div>
</template>
