<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { ArrowLeft, Save, Send } from 'lucide-vue-next'
import { computed, nextTick, ref } from 'vue'
import { useRouter } from 'vue-router'

import Page from '@/components/global-layout/basic-page.vue'
import { Button } from '@/components/ui/button'

import InvoiceEditorLayout from './components/invoice-editor-layout.vue'
import InvoiceForm from './components/invoice-form.vue'
import InvoicePreview from './components/invoice-preview.vue'

const router = useRouter()

const formRef = ref<InstanceType<typeof InvoiceForm> | null>(null)
const isSubmitting = ref(false)

const formValues = computed(() => {
  const values = formRef.value?.values || {}
  // For new invoices, show zero amounts
  return {
    ...values,
    subtotal: 0,
    total_vat_0: 0,
    total_vat_9: 0,
    total_vat_21: 0,
    total: 0,
  }
})

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
        <ArrowLeft class="mr-2 size-4" />
        Back
      </Button>
    </template>

    <InvoiceEditorLayout :is-loading="isSubmitting">
      <template #form>
        <InvoiceForm ref="formRef" :invoice="null" @close="handleClose" />
      </template>

      <template #preview>
        <InvoicePreview :form-values="formValues" :is-loading="isSubmitting" />
      </template>

      <template #actions>
        <Button variant="outline" @click="handleSave" :disabled="isSubmitting">
          <Save class="mr-2 size-4" />
          Save Draft
        </Button>
        <Button @click="handleSaveAndSend" :disabled="isSubmitting">
          <Send class="mr-2 size-4" />
          Save & Send
        </Button>
      </template>
    </InvoiceEditorLayout>
  </Page>
</template>
