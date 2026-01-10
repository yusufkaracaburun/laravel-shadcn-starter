<script setup lang="ts">
import type { IItem } from '@/pages/items/models/items'

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

interface Props {
  item: IItem
}

defineProps<Props>()

// Format price - handles both Money object and number
function formatPrice(
  price: number | { formatted: string } | undefined,
): string {
  if (!price)
    return '—'
  // Handle Money object from backend
  if (typeof price === 'object' && 'formatted' in price) {
    return price.formatted
  }
  // Handle number (backward compatibility)
  if (typeof price === 'number') {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(price)
  }
  return '—'
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Item Information</CardTitle>
      <CardDescription>Basic item details</CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Name
        </div>
        <div class="text-base">
          {{ item.name }}
        </div>
      </div>
      <div v-if="item.description">
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Description
        </div>
        <div class="text-base">
          {{ item.description }}
        </div>
      </div>
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Unit Price
        </div>
        <div class="text-base">
          {{ formatPrice(item.unit_price) }}
        </div>
      </div>
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          VAT Rate
        </div>
        <div class="text-base">
          {{ item.vat_rate }}%
        </div>
      </div>
      <div v-if="item.unit">
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Unit
        </div>
        <div class="text-base">
          {{ item.unit }}
        </div>
      </div>
    </CardContent>
  </Card>
</template>
