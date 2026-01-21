<script setup lang="ts">
import type { ICustomer } from '@/pages/customers/models/customers'

import Badge from '@/components/ui/badge/Badge.vue'
import {
  Building2Icon,
  CalendarIcon,
  MailIcon,
  PhoneIcon,
} from '@/composables/use-icons.composable'

interface Props {
  customer: ICustomer
  createdAt: string
  updatedAt: string
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
  <div class="space-y-6">
    <!-- Basic Information -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold">Basisinformatie</h3>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <div class="text-sm font-medium text-muted-foreground mb-1">Naam</div>
          <div class="text-base">{{ customer.name }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-muted-foreground mb-1">
            Klantnummer
          </div>
          <div class="text-base">{{ customer.number }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-muted-foreground mb-1">Type</div>
          <div class="text-base">
            <Badge
              :variant="customer.type === 'business' ? 'default' : 'secondary'"
            >
              {{ customerTypeLabel }}
            </Badge>
          </div>
        </div>
        <div>
          <div class="text-sm font-medium text-muted-foreground mb-1">
            Status
          </div>
          <div class="text-base">
            <Badge variant="secondary">{{ customerStatusLabel }}</Badge>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Information -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold">Contactgegevens</h3>
      <div class="grid gap-4 md:grid-cols-2">
        <div v-if="customer.email">
          <div
            class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
          >
            <MailIcon class="size-4" />
            E-mailadres
          </div>
          <div class="text-base">{{ customer.email }}</div>
        </div>
        <div v-if="customer.phone">
          <div
            class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
          >
            <PhoneIcon class="size-4" />
            Telefoonnummer
          </div>
          <div class="text-base">{{ customer.phone }}</div>
        </div>
      </div>
    </div>

    <!-- Address -->
    <div v-if="formattedAddress.length > 0" class="space-y-4">
      <h3 class="text-lg font-semibold">Adres</h3>
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">Adres</div>
        <div class="text-base space-y-0.5">
          <div v-for="(line, index) in formattedAddress" :key="index">
            {{ line }}
          </div>
        </div>
      </div>
    </div>

    <!-- Business Information -->
    <div
      v-if="customer.type === 'business'"
      class="space-y-4"
    >
      <h3 class="text-lg font-semibold">Bedrijfsgegevens</h3>
      <div class="grid gap-4 md:grid-cols-2">
        <div v-if="customer.kvk_number">
          <div
            class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
          >
            <Building2Icon class="size-4" />
            KVK-nummer
          </div>
          <div class="text-base">{{ customer.kvk_number }}</div>
        </div>
        <div v-if="customer.vat_number">
          <div class="text-sm font-medium text-muted-foreground mb-1">
            Btw-identificatienummer
          </div>
          <div class="text-base">{{ customer.vat_number }}</div>
        </div>
        <div v-if="customer.iban_number">
          <div class="text-sm font-medium text-muted-foreground mb-1">
            IBAN-nummer
          </div>
          <div class="text-base">{{ customer.iban_number }}</div>
        </div>
      </div>
    </div>

    <!-- Timestamps -->
    <div class="space-y-4">
      <h3 class="text-lg font-semibold">Systeeminformatie</h3>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <div
            class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
          >
            <CalendarIcon class="size-4" />
            Aangemaakt op
          </div>
          <div class="text-base">{{ createdAt }}</div>
        </div>
        <div>
          <div
            class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
          >
            <CalendarIcon class="size-4" />
            Bijgewerkt op
          </div>
          <div class="text-base">{{ updatedAt }}</div>
        </div>
        <div>
          <div class="text-sm font-medium text-muted-foreground mb-1">
            Klant ID
          </div>
          <div class="text-base">#{{ customer.id }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
