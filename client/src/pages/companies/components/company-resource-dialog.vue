<script lang="ts" setup>
import type { Company } from '@/services/companies.service'

import CompanyForm from './company-form.vue'

const props = defineProps<{
  company: Company | null
}>()
defineEmits(['close'])

const company = computed(() => props.company)
const title = computed(() =>
  company.value?.id ? 'Edit Company' : 'New Company',
)
const description = computed(() =>
  company.value?.id
    ? `Edit company ${company.value.name}`
    : 'Create a new company',
)
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>
        {{ title }}
      </UiDialogTitle>
      <UiDialogDescription>
        {{ description }}
      </UiDialogDescription>
    </UiDialogHeader>
    <CompanyForm class="mt-2" :company="company" @close="$emit('close')" />
  </div>
</template>
