<script lang="ts" setup>
import type { ICompany } from '@/pages/companies/models/companies'

import { Dialog, DialogContent } from '@/components/ui/dialog'

import CompanyResourceDialog from './company-resource-dialog.vue'

interface ICompanyEditDialogProps {
  company: ICompany
  open: boolean
}

const props = defineProps<ICompanyEditDialogProps>()

const emits = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'close'): void
}>()

function handleClose() {
  emits('update:open', false)
  emits('close')
}
</script>

<template>
  <Dialog :open="props.open" @update:open="emits('update:open', $event)">
    <DialogContent
      class="top-10 right-10 bottom-10 left-auto translate-x-0 translate-y-0 sm:max-w-[425px]"
    >
      <CompanyResourceDialog :company="props.company" @close="handleClose" />
    </DialogContent>
  </Dialog>
</template>
