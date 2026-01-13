<script setup lang="ts">
import type { ICustomer } from '@/pages/customers/models/customers'

import Badge from '@/components/ui/badge/Badge.vue'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Building2Icon,
  MailIcon,
  PhoneIcon,
} from '@/composables/use-icons.composable'

interface Props {
  customer: ICustomer
}

const props = defineProps<Props>()

const customerTypeLabel = computed(() => {
  return props.customer.type === 'business' ? 'Business' : 'Private'
})

const formattedAddress = computed(() => {
  const parts: string[] = []
  if (props.customer.address) {
    parts.push(props.customer.address)
  }
  if (props.customer.zipcode) {
    parts.push(props.customer.zipcode)
  }
  if (props.customer.city) {
    parts.push(props.customer.city)
  }
  if (props.customer.country) {
    parts.push(props.customer.country)
  }
  return parts.length > 0 ? parts.join(', ') : null
})
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Customer Information</CardTitle>
      <CardDescription>Basic customer details</CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Name
        </div>
        <div class="text-base">
          {{ customer.name }}
        </div>
      </div>
      <div v-if="customer.email">
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Email
        </div>
        <div class="text-base">
          {{ customer.email }}
        </div>
      </div>
      <div v-if="customer.phone">
        <div
          class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
        >
          <PhoneIcon class="size-4" />
          Phone
        </div>
        <div class="text-base">
          {{ customer.phone }}
        </div>
      </div>
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Type
        </div>
        <div class="text-base">
          <Badge
            :variant="customer.type === 'business' ? 'default' : 'secondary'"
          >
            {{ customerTypeLabel }}
          </Badge>
        </div>
      </div>
      <div v-if="formattedAddress">
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Address
        </div>
        <div class="text-base">
          {{ formattedAddress }}
        </div>
      </div>
      <div v-if="customer.type === 'business' && customer.kvk_number">
        <div
          class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
        >
          <Building2Icon class="size-4" />
          KVK Number
        </div>
        <div class="text-base">
          {{ customer.kvk_number }}
        </div>
      </div>
      <div v-if="customer.type === 'business' && customer.vat_number">
        <div class="text-sm font-medium text-muted-foreground mb-1">
          VAT Number
        </div>
        <div class="text-base">
          {{ customer.vat_number }}
        </div>
      </div>
      <div v-if="customer.type === 'business' && customer.iban_number">
        <div class="text-sm font-medium text-muted-foreground mb-1">
          IBAN Number
        </div>
        <div class="text-base">
          {{ customer.iban_number }}
        </div>
      </div>
    </CardContent>
  </Card>
</template>
