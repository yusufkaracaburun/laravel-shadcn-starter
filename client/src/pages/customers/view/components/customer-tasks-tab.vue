<script setup lang="ts">
import type { ICustomer } from '@/pages/customers/models/customers'

interface Props {
  customer: ICustomer
}

defineProps<Props>()
</script>

<template>
  <div class="flex items-center justify-center py-12">
    <div class="text-center space-y-2">
      <p class="text-sm text-muted-foreground">
        Taken en notities functionaliteit wordt binnenkort toegevoegd.
      </p>
    </div>
  </div>
</template>
