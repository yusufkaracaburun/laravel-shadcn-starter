<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { ArrowLeft } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import Page from '@/components/global-layout/basic-page.vue'
import { Button } from '@/components/ui/button'

import InvoiceForm from './components/invoice-form.vue'

const router = useRouter()

function handleClose() {
  router.push('/invoices')
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

    <div class="max-w-2xl">
      <InvoiceForm :invoice="null" @close="handleClose" />
    </div>
  </Page>
</template>
