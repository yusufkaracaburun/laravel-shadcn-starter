<script setup lang="ts">
import type { testusers } from '../../../../tests/.data/users.data'

defineProps<{
  testusers: typeof testusers
  loading: boolean
  onQuickLogin: (userKey: keyof typeof testusers) => Promise<void> | void
}>()
</script>

<template>
  <UiCard class="w-full max-w-sm">
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
          @click="onQuickLogin(key as keyof typeof testusers)"
        >
          <UiSpinner v-if="loading" class="mr-2" />
          <span class="font-medium">{{ user.name }}</span>
          <span class="ml-auto text-xs text-muted-foreground">{{ user.email }}</span>
        </UiButton>
      </div>
    </UiCardContent>
  </UiCard>
</template>
