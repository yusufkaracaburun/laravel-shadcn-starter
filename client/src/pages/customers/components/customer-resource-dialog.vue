<script lang="ts" setup>
import type { ICustomer } from '../models/customers'

import CustomerForm from './customer-form.vue'

interface ICustomerResourceDialogProps {
  customer: ICustomer | null
}

const props = defineProps<ICustomerResourceDialogProps>()

const emits = defineEmits<{
  close: []
}>()

const title = computed(() =>
  props.customer ? 'Edit Customer' : 'New Customer',
)
const description = computed(() =>
  props.customer
    ? `Update customer information for ${props.customer.name}.`
    : 'Add a new customer to the system. Fill in the required information below.',
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
    <CustomerForm
      :customer="props.customer"
      class="mt-2"
      @close="emits('close')"
    />
  </div>
</template>
