<script setup lang="ts">
import { useAuth } from '@/composables/use-auth'
import LoginForm4 from '@/pages/auth/components/LoginForm4.vue'
import env from '@/utils/env'

import { testusers } from '../../../tests/.data/users.data'
import QuickLoginCard from './components/quick-login-card.vue'

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
  <div
    class="bg-muted flex min-h-svh flex-col items-center justify-center p-6 md:p-10"
  >
    <div class="w-full max-w-sm md:max-w-4xl">
      <LoginForm4 />
    </div>
    <QuickLoginCard
      v-if="isLocal"
      :testusers="testusers"
      :loading="loading"
      :on-quick-login="quickLogin"
    />
  </div>
</template>
