<script lang="ts" setup>
import { useRouter } from 'vue-router'

import { Button } from '@/components/ui/button'
import { FilePlusIcon } from '@/composables/use-icons'

const router = useRouter()

function handleCreate() {
  router.push('/invoices/create')
}
</script>

<template>
  <Button @click="handleCreate">
    <FilePlusIcon />
    Create Invoice
  </Button>
</template>
