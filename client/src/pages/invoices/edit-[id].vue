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
import { useGetInvoicePrerequisitesQuery, useGetInvoiceQuery } from '@/services/invoices.service'

import InvoiceEditorLayout from './components/invoice-editor-layout.vue'
import InvoiceForm from './components/invoice-form.vue'
import InvoicePreview from './components/invoice-preview.vue'

const route = useRoute()
const router = useRouter()

const invoiceId = computed(() => Number(route.params.id))

const {
  data: invoiceResponse,
  isLoading,
  isError,
  error,
} = useGetInvoiceQuery(invoiceId, {
  include: ['items'],
})

// Fetch prerequisites to get customers (same as create page)
const { data: prerequisitesResponse } = useGetInvoicePrerequisitesQuery()
const prerequisites = computed(() => prerequisitesResponse.value?.data ?? null)

// Extract customers from prerequisites
const customers = computed(() => {
  if (!prerequisites.value?.customers) return []
  const customersData = prerequisites.value.customers
  return Array.isArray(customersData) ? customersData : ((customersData as any).data ?? [])
})

const invoice = computed(() => invoiceResponse.value?.data ?? null)

const formRef = ref<InstanceType<typeof InvoiceForm> | null>(null)
const isSubmitting = ref(false)

const currentFormValues = ref({})
const currentFormItems = ref([])

watch(
  invoice,
  (newInvoice) => {
    if (newInvoice) {
      currentFormValues.value = { ...newInvoice }
      currentFormItems.value = newInvoice.items || []
    }
  },
  { immediate: true },
)

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
        <InvoiceForm
          ref="formRef"
          :invoice="currentFormValues"
          @close="handleClose"
          @update:formValues="(values) => (currentFormValues = values)"
          @update:formItems="(items) => (currentFormItems = items)"
        />
      </template>

      <template #preview>
        <InvoicePreview
          :form-values="currentFormValues"
          :items="currentFormItems"
          :customers="customers"
          :is-loading="isSubmitting"
        />
      </template>

      <template #actions>
        <Button variant="outline" :disabled="isSubmitting" @click="handleUpdate">
          <Save class="mr-2 size-4" />
          Update
        </Button>
        <Button :disabled="isSubmitting" @click="handleUpdateAndSend">
          <Send class="mr-2 size-4" />
          Update & Send
        </Button>
      </template>
    </InvoiceEditorLayout>
  </Page>
</template>
