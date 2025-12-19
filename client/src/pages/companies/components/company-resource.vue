<script lang="ts" setup>
import type { Company } from '@/services/companies.service'

import CompanyForm from './company-form.vue'

const props = defineProps<{
  company?: Company | null
}>()

defineEmits(['close'])

const title = computed(() =>
  props.company ? 'Edit Company' : 'Create New Company',
)
const description = computed(() =>
  props.company
    ? `Update company information for ${props.company.name}.`
    : 'Add a new company to the system. Fill in the required information below.',
)
</script>

<template>
  <UiDrawerHeader>
    <UiDrawerTitle>
      {{ title }}
    </UiDrawerTitle>
    <UiDrawerDescription>
      {{ description }}
    </UiDrawerDescription>
  </UiDrawerHeader>

  <CompanyForm :company="company" class="mt-4" @close="$emit('close')" />
</template>
