<script setup lang="ts">
import type { Customer } from '@/services/customers.service'

import Loading from '@/components/loading.vue'

import CustomerCard from './customer-card.vue'

interface Props {
  customers: Customer[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})
</script>

<template>
  <div v-if="loading" class="flex items-center justify-center min-h-[400px]">
    <Loading />
  </div>
  <div v-else-if="customers.length === 0" class="flex items-center justify-center min-h-[400px]">
    <div class="text-center">
      <p class="text-muted-foreground">No customers found.</p>
    </div>
  </div>
  <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    <CustomerCard v-for="customer in customers" :key="customer.id" :customer="customer" />
  </div>
</template>
