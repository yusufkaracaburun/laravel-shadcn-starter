<script lang="ts" setup>
import type { ICustomer } from '@/pages/customers/models/customers'

import { Dialog, DialogContent } from '@/components/ui/dialog'

import CustomerResourceDialog from './customer-resource-dialog.vue'

interface ICustomerEditDialogProps {
  customer: ICustomer
  open: boolean
}

const props = defineProps<ICustomerEditDialogProps>()

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
      class="top-5 right-5 bottom-5 left-auto translate-x-0 translate-y-0 sm:max-w-[425px]"
    >
      <CustomerResourceDialog :customer="props.customer" @close="handleClose" />
    </DialogContent>
  </Dialog>
</template>
