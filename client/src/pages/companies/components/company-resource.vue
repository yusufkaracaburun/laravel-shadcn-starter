<script lang="ts" setup>
import type { ICompany } from '@/pages/companies/models/companies'

import CompanyForm from './company-form.vue'

const props = defineProps<{
  company?: ICompany | null
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
