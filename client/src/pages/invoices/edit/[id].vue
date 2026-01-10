<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, nextTick, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import type { TInvoiceForm, TInvoiceItem } from '@/pages/invoices/data/schema'

import Page from '@/components/global-layout/basic-page.vue'
import { Button } from '@/components/ui/button'
import { ArrowLeftIcon, SendIcon } from '@/composables/use-icons.composable'
import { useInvoices } from '@/pages/invoices/composables/use-invoices.composable'
import { useToast } from '@/composables/use-toast.composable'
import InvoiceForm from '@/pages/invoices/components/invoice-form.vue'
import InvoiceEditorLayout from '@/pages/invoices/edit/components/invoice-editor-layout.vue'

const {
  invoiceId,
  invoiceByIdResponse: invoiceResponse,
  isLoadingInvoiceById: isLoading,
  isErrorInvoiceById: isError,
  errorInvoiceById: error,
  fetchInvoiceByIdData,
} = useInvoices()

const route = useRoute()
const router = useRouter()
const { toast, showSuccess } = useToast()

const invoice = computed(() => invoiceResponse.value?.data ?? null)

const formRef = ref<InstanceType<typeof InvoiceForm> | null>(null)
const isSubmitting = ref(false)

const currentFormValues = ref<TInvoiceForm | any>(null) // This will be updated via v-model from InvoiceForm
const currentFormItems = ref<TInvoiceItem[]>([]) // This will be updated via @update:form-items from InvoiceForm

// Initial setup for currentFormValues and currentFormItems when invoice data loads
watch(
  invoice,
  (newInvoice) => {
    if (newInvoice) {
      currentFormValues.value = { ...newInvoice }
      // currentFormItems.value
      // = (newInvoice.items as any)?.data || newInvoice.items || []
    }
  },
  { immediate: true },
)

function handleClose() {
  router.push({
    name: '/invoices/view/[id]',
    params: { id: invoiceId.value?.toString() ?? '' },
  })
}

async function handleUpdate() {
  if (!formRef.value)
    return
  isSubmitting.value = true
  try {
    await formRef.value.handleSubmit()
    showSuccess('Invoice updated')
  } catch (validationError) {
    console.error('Validation Error:', validationError)
    toast({
      title: 'Validation Error',
      description: 'Please correct the form errors.',
      variant: 'destructive',
    })
  } finally {
    isSubmitting.value = false
  }
}

async function handleUpdateAndSend() {
  if (!formRef.value)
    return
  isSubmitting.value = true
  try {
    formRef.value.setFieldValue('status', 'sent')
    await nextTick()
    await formRef.value.handleSubmit()
    showSuccess('Invoice updated and sent')
  } catch (validationError) {
    console.error('Validation Error:', validationError)
    toast({
      title: 'Validation Error',
      description: 'Please correct the form errors.',
      variant: 'destructive',
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <Page title="Edit Invoice" description="Edit invoice details" sticky>
    <template #actions>
      <Button variant="outline" size="sm" @click="handleClose">
        <ArrowLeftIcon class="mr-2 size-4" />
        Back
      </Button>
      <Button variant="outline" :disabled="isSubmitting" @click="handleUpdate">
        <Save class="mr-2 size-4" />
        Update
      </Button>
      <Button :disabled="isSubmitting" @click="handleUpdateAndSend">
        <SendIcon class="mr-2 size-4" />
        Update & Send
      </Button>
    </template>

    <InvoiceEditorLayout
      :is-loading="isLoading"
      :is-error="isError"
      :error-object="error"
      :on-retry="fetchInvoiceByIdData"
    >
      <template #form>
        <InvoiceForm
          ref="formRef"
          v-model:model-value="currentFormValues"
          :invoice-id="invoiceId"
          @update:form-items="(items) => (currentFormItems = items)"
        />
      </template>

      <!-- <template #preview>
        <InvoicePreview :form-values="currentFormValues" :items="currentFormItems" :customers="customers"
          :is-loading="isSubmitting" />
      </template> -->
    </InvoiceEditorLayout>
  </Page>
</template>
