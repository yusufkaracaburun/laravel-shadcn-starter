<script lang="ts" setup>
import { useRouter } from 'vue-router'

import { FilePlusIcon } from '@/composables/use-icons'

import { Button } from '@/components/ui/button'

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
