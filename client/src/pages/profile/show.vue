<script setup lang="ts">
import { computed } from 'vue'

import { useAuthStore } from '@/stores/auth'
import UpdateProfileInformationForm from './partials/update-profile-information-form.vue'
import UpdatePasswordForm from './partials/update-password-form.vue'

const authStore = useAuthStore()
const user = computed(() => authStore.user)
</script>

<template>
  <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="mb-6">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
        Profile Settings
      </h2>
    </div>

    <div class="space-y-6">
      <UpdateProfileInformationForm :user="user" />

      <UpdatePasswordForm />
    </div>
  </div>
</template>

