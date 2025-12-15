<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { ArrowLeft } from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import { Button } from '@/components/ui/button'
import { useGetInvoiceQuery } from '@/services/invoices.service'

import InvoiceForm from '../components/invoice-form.vue'

const route = useRoute()
const router = useRouter()

const invoiceId = computed(() => Number(route.params.id))

const { data: invoiceResponse, isLoading, isError, error } = useGetInvoiceQuery(invoiceId)

const invoice = computed(() => invoiceResponse.value?.data ?? null)

function handleClose() {
  router.push({ name: '/invoices/[id]', params: { id: invoiceId.value.toString() } })
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

    <div v-else-if="invoice" class="max-w-2xl">
      <InvoiceForm :invoice="invoice" @close="handleClose" />
    </div>
  </Page>
</template>
