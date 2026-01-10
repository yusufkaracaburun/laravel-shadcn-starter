<script lang="ts" setup>
import type { ICustomer } from '../models/customers'

import CustomerForm from './customer-form.vue'

const props = defineProps<{
  customer: ICustomer | null
}>()
defineEmits(['close'])

const customer = computed(() => props.customer)
const title = computed(() =>
  customer.value?.id ? 'Edit Customer' : 'New Customer',
)
const description = computed(() =>
  customer.value?.id
    ? `Edit customer ${customer.value.id}`
    : 'Create new customer',
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
    <CustomerForm class="mt-2" :customer="customer" @close="$emit('close')" />
  </div>
</template>
