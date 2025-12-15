<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { ArrowLeft, Save, Send } from 'lucide-vue-next'
import { computed, nextTick, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import { Button } from '@/components/ui/button'
import { useGetInvoiceQuery } from '@/services/invoices.service'

import InvoiceEditorLayout from '../components/invoice-editor-layout.vue'
import InvoiceForm from '../components/invoice-form.vue'
import InvoicePreview from '../components/invoice-preview.vue'

const route = useRoute()
const router = useRouter()

const invoiceId = computed(() => Number(route.params.id))

const { data: invoiceResponse, isLoading, isError, error } = useGetInvoiceQuery(invoiceId, {
  include: ['items'],
})

const invoice = computed(() => invoiceResponse.value?.data ?? null)

const formRef = ref<InstanceType<typeof InvoiceForm> | null>(null)
const isSubmitting = ref(false)

const formValues = computed(() => {
  const values = formRef.value?.values || {}
  // Include financial data from invoice if editing
  if (invoice.value) {
    return {
      ...values,
      subtotal: invoice.value.subtotal,
      total_vat_0: invoice.value.total_vat_0,
      total_vat_9: invoice.value.total_vat_9,
      total_vat_21: invoice.value.total_vat_21,
      total: invoice.value.total,
    }
  }
  return values
})

const formItems = computed(() => {
  // For existing invoices, use items from API (loaded via invoice.items)
  // For form edits, use items from formRef if available
  return formRef.value?.items || invoice.value?.items || []
})

function handleClose() {
  router.push({ name: '/invoices/[id]', params: { id: invoiceId.value.toString() } })
}

async function handleUpdate() {
  if (!formRef.value) return
  isSubmitting.value = true
  try {
    await formRef.value.handleSubmit()
  } catch (error) {
    console.error('Error updating invoice:', error)
  } finally {
    isSubmitting.value = false
  }
}

async function handleUpdateAndSend() {
  if (!formRef.value) return
  isSubmitting.value = true
  try {
    // Update status to 'sent' before submitting
    formRef.value.setFieldValue('status', 'sent')
    // Wait a tick for the value to update
    await nextTick()
    await formRef.value.handleSubmit()
  } catch (error) {
    console.error('Error updating and sending invoice:', error)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <Page title="Edit Invoice" description="Edit invoice details" sticky>
    <template #actions>
      <Button variant="outline" size="sm" @click="handleClose">
        <ArrowLeft class="mr-2 size-4" />
        Back
      </Button>
    </template>

    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <Loading />
    </div>

    <Error
      v-else-if="isError"
      :error="error"
      title="Failed to load invoice"
      description="We couldn't load the invoice details. Please try again."
    />

    <InvoiceEditorLayout v-else-if="invoice" :is-loading="isSubmitting">
      <template #form>
        <InvoiceForm ref="formRef" :invoice="invoice" @close="handleClose" />
      </template>

      <template #preview>
        <InvoicePreview :form-values="formValues" :items="formItems" :is-loading="isSubmitting" />
      </template>

      <template #actions>
        <Button variant="outline" @click="handleUpdate" :disabled="isSubmitting">
          <Save class="mr-2 size-4" />
          Update
        </Button>
        <Button @click="handleUpdateAndSend" :disabled="isSubmitting">
          <Send class="mr-2 size-4" />
          Update & Send
        </Button>
      </template>
    </InvoiceEditorLayout>
  </Page>
</template>
