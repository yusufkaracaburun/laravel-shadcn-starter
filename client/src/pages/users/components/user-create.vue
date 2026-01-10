<script lang="ts" setup>
import { UserRoundPlusIcon } from '@/composables/use-icons.composable'

import UserResource from './user-resource.vue'

const isOpen = ref(false)
</script>

<template>
  <UiDrawer v-model:open="isOpen" direction="right">
    <UiDrawerTrigger as-child>
      <UiButton>
        <UserRoundPlusIcon />
        Create User
      </UiButton>
    </UiDrawerTrigger>

    <UiDrawerContent class="px-4 pb-4">
      <div class="overflow-y-auto max-h-[calc(100vh-4rem)]">
        <UserResource @close="isOpen = false" />
      </div>
    </UiDrawerContent>
  </UiDrawer>
</template>
