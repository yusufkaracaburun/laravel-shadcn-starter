<script setup lang="ts">
import { useAuth } from '@/pages/auth/composables/use-auth.composable'
import env from '@/utils/env'

import { testusers } from '../../../tests/.data/users.data'
import AuthTitle from './components/auth-title.vue'
import LoginForm from './components/login-form.vue'
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
  <div class="flex items-center justify-center min-h-screen p-4 min-w-screen">
    <main class="flex flex-col gap-4">
      <AuthTitle />
      <LoginForm />

      <QuickLoginCard
        v-if="isLocal"
        :testusers="testusers"
        :loading="loading"
        :on-quick-login="quickLogin"
      />
    </main>
  </div>
</template>
