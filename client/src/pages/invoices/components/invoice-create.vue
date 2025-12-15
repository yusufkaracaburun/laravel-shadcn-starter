<script lang="ts" setup>
import { FilePlus } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import { Button } from '@/components/ui/button'

const router = useRouter()

function handleCreate() {
  router.push('/invoices/create')
}
</script>

<template>
  <Button @click="handleCreate">
    <FilePlus />
    Create Invoice
  </Button>
</template>
