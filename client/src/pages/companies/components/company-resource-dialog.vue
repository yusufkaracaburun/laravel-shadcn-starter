<script lang="ts" setup>
import type { ICompany } from '@/pages/companies/models/companies'

import CompanyForm from './company-form.vue'

interface ICompanyResourceDialogProps {
  company: ICompany | null
}

const props = defineProps<ICompanyResourceDialogProps>()

const emits = defineEmits<{
  close: []
}>()

const title = computed(() => (props.company ? 'Edit Company' : 'New Company'))
const description = computed(() =>
  props.company
    ? `Update company information for ${props.company.name}.`
    : 'Add a new company to the system. Fill in the required information below.',
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
    <CompanyForm
      :company="props.company"
      class="mt-2"
      @close="emits('close')"
    />
  </div>
</template>
