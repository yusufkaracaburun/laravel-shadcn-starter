<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

import {
  ArrowLeftIcon,
  SaveIcon,
  SendIcon,
} from '@/composables/use-icons'

import Page from '@/components/global-layout/basic-page.vue'
import { Button } from '@/components/ui/button'
import { useGetInvoicePrerequisitesQuery } from '@/services/invoices.service'

import type { TInvoiceForm } from './data/schema'

import InvoiceForm from './components/invoice-form.vue'
import InvoiceEditorLayout from './edit/components/invoice-editor-layout.vue'

const router = useRouter()

// Fetch prerequisites when creating a new invoice
const { data: prerequisitesResponse } = useGetInvoicePrerequisitesQuery()
const prerequisites = computed(() => prerequisitesResponse.value?.data ?? null)

// Extract items from prerequisites (ItemCollection serializes to { data: Item[] })
const items = computed(() => {
  if (!prerequisites.value?.items) return []
  // Handle both array format and ResourceCollection format ({ data: [...] })
  const itemsData = prerequisites.value.items
  return Array.isArray(itemsData) ? itemsData : ((itemsData as any).data ?? [])
})

// Extract customers from prerequisites (CustomerCollection serializes to { data: Customer[] })
const customers = computed(() => {
  if (!prerequisites.value?.customers) return []
  // Handle both array format and ResourceCollection format ({ data: [...] })
  const customersData = prerequisites.value.customers
  return Array.isArray(customersData)
    ? customersData
    : ((customersData as any).data ?? [])
})

const formRef = ref<InstanceType<typeof InvoiceForm> | null>(null)
const isSubmitting = ref(false)

const currentFormValues = ref<TInvoiceForm>({
  customer_id: 0, // Default customer_id
  invoice_number: prerequisites.value?.next_invoice_number ?? '',
  date: new Date().toISOString().split('T')[0], // Current date
  due_days: 30,
  date_due: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
    .toISOString()
    .split('T')[0], // 30 days from now
  status: 'draft',
  items: [],
  notes: null,
})
const currentFormItems = ref([])

watch(
  prerequisites,
  (newVal) => {
    if (newVal?.next_invoice_number) {
      currentFormValues.value.invoice_number = newVal.next_invoice_number
    }
  },
  { immediate: true },
)

function handleClose() {
  router.push('/invoices')
}

async function handleSave() {
  if (!formRef.value) return
  isSubmitting.value = true
  try {
    await formRef.value.handleSubmit()
  } catch (error) {
    console.error('Error saving invoice:', error)
  } finally {
    isSubmitting.value = false
  }
}

async function handleSaveAndSend() {
  if (!formRef.value) return
  isSubmitting.value = true
  try {
    // Update status to 'sent' before submitting
    formRef.value.setFieldValue('status', 'sent')
    // Wait a tick for the value to update
    await nextTick()
    await formRef.value.handleSubmit()
  } catch (error) {
    console.error('Error saving and sending invoice:', error)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <Page title="Create Invoice" description="Create a new invoice" sticky>
    <template #actions>
      <Button variant="outline" size="sm" @click="handleClose">
        <ArrowLeftIcon class="mr-2 size-4" />
        Back
      </Button>
    </template>

    <InvoiceEditorLayout :is-loading="isSubmitting">
      <template #form>
        <InvoiceForm
          ref="formRef"
          v-model:model-value="currentFormValues"
          :next-invoice-number="prerequisites?.next_invoice_number ?? null"
          :items="items"
          :customers="customers"
          @close="handleClose"
          @update:form-items="(items) => (currentFormItems = items)"
        />
      </template>

      <template #actions>
        <Button variant="outline" :disabled="isSubmitting" @click="handleSave">
          <SaveIcon class="mr-2 size-4" />
          Save Draft
        </Button>
        <Button :disabled="isSubmitting" @click="handleSaveAndSend">
          <SendIcon class="mr-2 size-4" />
          Save & Send
        </Button>
      </template>
    </InvoiceEditorLayout>
  </Page>
</template>
