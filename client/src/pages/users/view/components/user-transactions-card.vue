<script setup lang="ts">
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import Badge from '@/components/ui/badge/Badge.vue'

// Mock data - in real app, this would come from API
const transactions = [
  {
    id: 1,
    product: 'Mock premium pack',
    status: 'pending',
    date: '12/10/2025',
  },
  {
    id: 2,
    product: 'Enterprise plan subscription',
    status: 'paid',
    date: '11/13/2025',
  },
  {
    id: 3,
    product: 'Basic plan subscription',
    status: 'failed',
    date: '10/15/2025',
  },
]

function getStatusVariant(status: string) {
  switch (status) {
    case 'paid':
      return 'default'
    case 'pending':
      return 'secondary'
    case 'failed':
      return 'destructive'
    default:
      return 'secondary'
  }
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Transaction History</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="space-y-4">
        <div
          v-for="transaction in transactions"
          :key="transaction.id"
          class="flex items-center justify-between py-2 border-b last:border-0"
        >
          <div class="flex-1">
            <p class="font-medium text-sm">
              {{ transaction.product }}
            </p>
          </div>
          <div class="flex items-center gap-4">
            <Badge :variant="getStatusVariant(transaction.status)">
              {{ transaction.status }}
            </Badge>
            <span class="text-sm text-muted-foreground">
              {{ transaction.date }}
            </span>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
