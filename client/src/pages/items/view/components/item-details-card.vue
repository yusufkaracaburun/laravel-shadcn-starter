<script setup lang="ts">
import type { IItem } from '@/pages/items/models/items'

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { CalendarIcon } from '@/composables/use-icons.composable'

interface Props {
  item: IItem
  createdAt: string
  updatedAt: string
}

defineProps<Props>()
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Item Details</CardTitle>
      <CardDescription>
        Item creation and update information
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Item ID
        </div>
        <div class="text-base">
          #{{ item.id }}
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
