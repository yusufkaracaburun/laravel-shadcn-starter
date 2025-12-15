<script setup lang="ts">
import { GalleryVerticalEnd } from 'lucide-vue-next'

import { useAuth } from '@/composables/use-auth'
import LoginForm2 from '@/pages/auth/components/LoginForm2.vue'
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
  }
  catch (error) {
    // Error handling is done in useAuth composable
    console.error('Quick login failed:', error)
  }
}
</script>

<template>
  <div class="grid min-h-svh lg:grid-cols-2">
    <div class="flex flex-col gap-4 p-6 md:p-10">
      <div class="flex justify-center gap-2 md:justify-start">
        <a href="#" class="flex items-center gap-2 font-medium">
          <div
            class="flex size-6 items-center justify-center rounded-md bg-primary text-primary-foreground"
          >
            <GalleryVerticalEnd class="size-4" />
          </div>
          Acme Inc.
        </a>
      </div>
      <div class="flex flex-1 items-center justify-center">
        <div class="w-full max-w-xs">
          <LoginForm2 />
        </div>
      </div>
      <QuickLoginCard
        v-if="isLocal"
        :testusers="testusers"
        :loading="loading"
        :on-quick-login="quickLogin"
      />
    </div>
    <div class="bg-muted relative hidden lg:block">
      <img
        src="/placeholder.png"
        alt=""
        class="absolute inset-0 h-full w-full object-cover dark:brightness-[0.2] dark:grayscale"
      >
    </div>
  </div>
</template>
