<script setup lang="ts">
import type { ICustomer } from '@/pages/customers/models/customers'

import DetailsField from '@/components/global-layout/components/details-page/details-field.vue'
import DetailsSection from '@/components/global-layout/components/details-page/details-section.vue'
import Badge from '@/components/ui/badge/Badge.vue'
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
    <DetailsSection title="Basisinformatie">
      <div class="grid gap-2.5 md:grid-cols-2">
        <DetailsField label="Naam" :value="customer.name" />
        <DetailsField label="Klantnummer" :value="customer.number" />
        <DetailsField label="Type">
          <Badge
            :variant="customer.type === 'business' ? 'default' : 'secondary'"
            class="text-xs"
          >
            {{ customerTypeLabel }}
          </Badge>
        </DetailsField>
        <DetailsField label="Status">
          <Badge variant="secondary" class="text-xs">
            {{ customerStatusLabel }}
          </Badge>
        </DetailsField>
      </div>
    </DetailsSection>

    <!-- Contact Information -->
    <DetailsSection title="Contactgegevens">
      <div class="grid gap-2.5 md:grid-cols-2">
        <DetailsField
          v-if="customer.email"
          label="E-mailadres"
          :value="customer.email"
          :icon="MailIcon"
        />
        <DetailsField
          v-if="customer.phone"
          label="Telefoonnummer"
          :value="customer.phone"
          :icon="PhoneIcon"
        />
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
    </DetailsSection>

    <!-- Business Information -->
    <DetailsSection
      v-if="customer.type === 'business'"
      title="Bedrijfsgegevens"
    >
      <div class="grid gap-2.5 md:grid-cols-2">
        <DetailsField
          v-if="customer.kvk_number"
          label="KVK-nummer"
          :value="customer.kvk_number"
          :icon="Building2Icon"
        />
        <DetailsField
          v-if="customer.vat_number"
          label="Btw-identificatienummer"
          :value="customer.vat_number"
        />
        <DetailsField
          v-if="customer.iban_number"
          label="IBAN-nummer"
          :value="customer.iban_number"
        />
      </div>
    </DetailsSection>

    <!-- Timestamps -->
    <DetailsSection title="Systeeminformatie">
      <div class="grid gap-2.5 md:grid-cols-2">
        <DetailsField
          label="Aangemaakt op"
          :value="customer.created_at"
          :icon="CalendarIcon"
        />
        <DetailsField
          label="Bijgewerkt op"
          :value="customer.updated_at"
          :icon="CalendarIcon"
        />
        <DetailsField label="Klant ID" :value="`#${customer.id}`" />
      </div>
    </DetailsSection>
  </div>
</template>
