<script setup lang="ts">
import type { IProduct } from '@/pages/products/models/products'

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { CalendarIcon } from '@/composables/use-icons.composable'

interface Props {
  product: IProduct
  createdAt: string
  updatedAt: string
}

defineProps<Props>()
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Product Details</CardTitle>
      <CardDescription>
        Product creation and update information
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Product ID
        </div>
        <div class="text-base">
          #{{ product.id }}
        </div>
      </div>
      <div>
        <div
          class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
        >
          <CalendarIcon class="size-4" />
          Created At
        </div>
        <div class="text-base">
          {{ createdAt }}
        </div>
      </div>
      <div>
        <div
          class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
        >
          <CalendarIcon class="size-4" />
          Updated At
        </div>
        <div class="text-base">
          {{ updatedAt }}
        </div>
      </div>
    </CardContent>
  </Card>
</template>
