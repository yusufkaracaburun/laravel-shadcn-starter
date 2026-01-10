<script lang="ts" setup>
import { Building2Icon } from '@/composables/use-icons.composable'

import CompanyResource from './company-resource.vue'

const isOpen = ref(false)
</script>

<template>
  <UiDrawer v-model:open="isOpen" direction="right">
    <UiDrawerTrigger as-child>
      <UiButton>
        <Building2Icon />
        Create Company
      </UiButton>
    </UiDrawerTrigger>

    <UiDrawerContent class="px-4 pb-4">
      <div class="overflow-y-auto max-h-[calc(100vh-4rem)]">
        <CompanyResource @close="isOpen = false" />
      </div>
    </UiDrawerContent>
  </UiDrawer>
</template>
