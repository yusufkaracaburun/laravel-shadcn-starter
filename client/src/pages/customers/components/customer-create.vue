<script lang="ts" setup>
import { UserPlus } from 'lucide-vue-next'

import CustomerResource from './customer-resource.vue'

const isOpen = ref(false)
</script>

<template>
  <UiDrawer v-model:open="isOpen" direction="right">
    <UiDrawerTrigger as-child>
      <UiButton>
        <UserPlus />
        Create Customer
      </UiButton>
    </UiDrawerTrigger>

    <UiDrawerContent class="px-4 pb-4">
      <div class="overflow-y-auto max-h-[calc(100vh-4rem)]">
        <CustomerResource @close="isOpen = false" />
      </div>
    </UiDrawerContent>
  </UiDrawer>
</template>

