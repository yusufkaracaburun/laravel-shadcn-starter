<script lang="ts" setup>
import type { ICustomer } from '../models/customers'

import CustomerForm from './customer-form.vue'

const props = defineProps<{
  customer?: ICustomer | null
}>()

defineEmits(['close'])

const title = computed(() =>
  props.customer ? 'Edit Customer' : 'Create New Customer',
)
const description = computed(() =>
  props.customer
    ? `Update customer information for ${props.customer.name}.`
    : 'Add a new customer to the system. Fill in the required information below.',
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

  <CustomerForm :customer="customer" class="mt-4" @close="$emit('close')" />
</template>
