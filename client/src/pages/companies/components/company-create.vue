<script lang="ts" setup>
import { Building2 } from 'lucide-vue-next'

import CompanyResource from './company-resource.vue'

const isOpen = ref(false)
</script>

<template>
  <UiDrawer v-model:open="isOpen" direction="right">
    <UiDrawerTrigger as-child>
      <UiButton>
        <Building2 />
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

