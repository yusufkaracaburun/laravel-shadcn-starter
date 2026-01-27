<script setup lang="ts">
import type { ICustomer } from '@/pages/customers/models/customers'

import Badge from '@/components/ui/badge/Badge.vue'
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Building2Icon,
  CalendarIcon,
  MailIcon,
  PhoneIcon,
} from '@/composables/use-icons.composable'

interface Props {
  customer: ICustomer
}

const props = defineProps<Props>()

const customerTypeLabel = computed(() => {
  return props.customer.type === 'business' ? 'Bedrijf' : 'Particulier'
})

const customerStatusLabel = computed(() => {
  const statusMap: Record<string, string> = {
    registered: 'Geregistreerd',
    active: 'Actief',
    inactive: 'Inactief',
  }
  return statusMap[props.customer.status] || props.customer.status
})

const formattedAddress = computed(() => {
  // Use formatted_address if available, otherwise fallback to manual construction
  if (props.customer.formatted_address?.multiline) {
    return props.customer.formatted_address.multiline.split('<br/>').filter(Boolean)
  }
  // Fallback to manual construction
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
  return parts
})
</script>

<template>
  <div class="space-y-2">
    <!-- Basic Information -->
    <Card class="border">
      <CardHeader class="pb-1.5 pt-2.5 px-4">
        <CardTitle class="text-sm font-semibold">
          Basisinformatie
        </CardTitle>
      </CardHeader>
      <CardContent class="px-4 pb-2.5">
        <div class="grid gap-2.5 md:grid-cols-2">
          <div>
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Naam
            </div>
            <div class="text-sm">
              {{ customer.name }}
            </div>
          </div>
          <div>
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Klantnummer
            </div>
            <div class="text-sm">
              {{ customer.number }}
            </div>
          </div>
          <div>
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Type
            </div>
            <div class="text-sm">
              <Badge
                :variant="customer.type === 'business' ? 'default' : 'secondary'"
                class="text-xs"
              >
                {{ customerTypeLabel }}
              </Badge>
            </div>
          </div>
          <div>
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Status
            </div>
            <div class="text-sm">
              <Badge variant="secondary" class="text-xs">
                {{ customerStatusLabel }}
              </Badge>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Contact Information -->
    <Card class="border">
      <CardHeader class="pb-1.5 pt-2.5 px-4">
        <CardTitle class="text-sm font-semibold">
          Contactgegevens
        </CardTitle>
      </CardHeader>
      <CardContent class="px-4 pb-2.5">
        <div class="grid gap-2.5 md:grid-cols-2">
          <div v-if="customer.email">
            <div
              class="text-xs font-medium text-muted-foreground mb-0.5 flex items-center gap-1.5"
            >
              <MailIcon class="size-3" />
              E-mailadres
            </div>
            <div class="text-sm">
              {{ customer.email }}
            </div>
          </div>
          <div v-if="customer.phone">
            <div
              class="text-xs font-medium text-muted-foreground mb-0.5 flex items-center gap-1.5"
            >
              <PhoneIcon class="size-3" />
              Telefoonnummer
            </div>
            <div class="text-sm">
              {{ customer.phone }}
            </div>
          </div>
          <div v-if="formattedAddress.length > 0" class="md:col-span-2">
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Adres
            </div>
            <div class="text-sm space-y-0.5">
              <div
                v-for="(line, index) in formattedAddress"
                :key="index"
              >
                {{ line }}
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Business Information -->
    <Card
      v-if="customer.type === 'business'"
      class="border"
    >
      <CardHeader class="pb-1.5 pt-2.5 px-4">
        <CardTitle class="text-sm font-semibold">
          Bedrijfsgegevens
        </CardTitle>
      </CardHeader>
      <CardContent class="px-4 pb-2.5">
        <div class="grid gap-2.5 md:grid-cols-2">
          <div v-if="customer.kvk_number">
            <div
              class="text-xs font-medium text-muted-foreground mb-0.5 flex items-center gap-1.5"
            >
              <Building2Icon class="size-3" />
              KVK-nummer
            </div>
            <div class="text-sm">
              {{ customer.kvk_number }}
            </div>
          </div>
          <div v-if="customer.vat_number">
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Btw-identificatienummer
            </div>
            <div class="text-sm">
              {{ customer.vat_number }}
            </div>
          </div>
          <div v-if="customer.iban_number">
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              IBAN-nummer
            </div>
            <div class="text-sm">
              {{ customer.iban_number }}
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Timestamps -->
    <Card class="border">
      <CardHeader class="pb-1.5 pt-2.5 px-4">
        <CardTitle class="text-sm font-semibold">
          Systeeminformatie
        </CardTitle>
      </CardHeader>
      <CardContent class="px-4 pb-2.5">
        <div class="grid gap-2.5 md:grid-cols-2">
          <div>
            <div
              class="text-xs font-medium text-muted-foreground mb-0.5 flex items-center gap-1.5"
            >
              <CalendarIcon class="size-3" />
              Aangemaakt op
            </div>
            <div class="text-sm">
              {{ customer.created_at }}
            </div>
          </div>
          <div>
            <div
              class="text-xs font-medium text-muted-foreground mb-0.5 flex items-center gap-1.5"
            >
              <CalendarIcon class="size-3" />
              Bijgewerkt op
            </div>
            <div class="text-sm">
              {{ customer.updated_at }}
            </div>
          </div>
          <div>
            <div class="text-xs font-medium text-muted-foreground mb-0.5">
              Klant ID
            </div>
            <div class="text-sm">
              #{{ customer.id }}
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
