<script setup lang="ts">
import { formatMoney } from '../utils/formatters'

interface IProps {
  subtotal: number
  totalVat0: number
  totalVat9: number
  totalVat21: number
  total: number
}

defineProps<IProps>()
</script>

<template>
  <!-- Totals Summary -->
  <div class="mt-4 rounded-lg border bg-muted/50 p-4">
    <div class="grid grid-cols-2 gap-4 md:grid-cols-5 text-sm">
      <div>
        <p class="text-muted-foreground">Subtotal</p>
        <p class="font-semibold">
          {{ formatMoney(subtotal) }}
        </p>
      </div>
      <div>
        <p class="text-muted-foreground">VAT 0%</p>
        <p class="font-semibold">
          {{ formatMoney(totalVat0) }}
        </p>
      </div>
      <div>
        <p class="text-muted-foreground">VAT 9%</p>
        <p class="font-semibold">
          {{ formatMoney(totalVat9) }}
        </p>
      </div>
      <div>
        <p class="text-muted-foreground">VAT 21%</p>
        <p class="font-semibold">
          {{ formatMoney(totalVat21) }}
        </p>
      </div>
      <div>
        <p class="text-muted-foreground">Total</p>
        <p class="font-bold text-lg">
          {{ formatMoney(total) }}
        </p>
      </div>
    </div>
  </div>
</template>
