<script lang="ts" setup>
import CustomerForm from '@/pages/customers/components/customer-form.vue'

const emits = defineEmits(['close', 'created'])

function handleClose() {
  emits('close')
}

function handleCreated() {
  emits('created')
  emits('close')
}
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>Add New Customer</UiDialogTitle>
      <UiDialogDescription>Add a new customer to your system.</UiDialogDescription>
    </UiDialogHeader>

    <CustomerForm :customer="null" @close="handleCreated" />
  </div>
</template>
