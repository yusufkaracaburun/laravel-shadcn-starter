<script setup lang="ts">
import { h } from 'vue'

import type { ICustomer } from '@/pages/customers/models/customers'

import DetailsSidebar from '@/components/global-layout/components/details-page/details-sidebar.vue'
import InfoItem from '@/components/global-layout/components/info-item.vue'
import InfoSection
  from '@/components/global-layout/components/info-section.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import { Button } from '@/components/ui/button'
import {
  BadgeCheckIcon,
  Building2Icon,
  CalendarIcon,
  CheckCircle2Icon,
  CircleIcon,
  MailIcon,
  PhoneIcon,
  PlusIcon,
  UserIcon,
} from '@/composables/use-icons.composable'
import { statuses } from '@/pages/customers/data/data'
import { formatDate } from '@/utils/date'

interface Props {
  customer: ICustomer
}

const props = defineProps<Props>()

defineEmits<{
  addContact: []
}>()

function getStatusInfo(status: string | null | undefined) {
  if (!status) {
    return null
  }

  return statuses.find((statusItem) => {
    return statusItem.value.toLowerCase() === status.toLowerCase()
  })
}

function getStatusVariant(status: string | null | undefined) {
  const statusInfo = getStatusInfo(status)
  if (!statusInfo) {
    return 'secondary'
  }

  switch (statusInfo.value) {
    case 'active':
      return 'default'
    case 'inactive':
      return 'destructive'
    case 'registered':
      return 'secondary'
    default:
      return 'secondary'
  }
}

const contacts = computed(() => {
  return props.customer.contacts ?? []
})

const formattedAddress = computed(() => {
  if (props.customer.formatted_address?.multiline) {
    return props.customer.formatted_address.multiline.split('<br/>').filter(Boolean)
  }
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

const formattedCreatedAt = computed(() => formatDate(props.customer.created_at))
const formattedUpdatedAt = computed(() => formatDate(props.customer.updated_at))

const customerTypeLabel = computed(() => {
  return props.customer.type === 'business' ? 'Business' : 'Private'
})

const relatedItems = computed(() => {
  return contacts.value.map(contact => ({
    id: contact.id,
    name: contact.name,
    icon: UserIcon,
    badge: contact.id === props.customer.primary_contact?.id ? () => h(BadgeCheckIcon, { class: 'size-3.5 text-blue-600' }) : undefined,
  }))
})
</script>

<template>
  <DetailsSidebar
    title="Customer"
    :subtitle="`Klantnummer: ${customer.number}`"
    :related-items="relatedItems"
    related-items-label="Contactpersonen"
    related-items-empty-text="Geen contactpersonen"
  >
    <template #header-title>
      {{ customer.name }}
    </template>

    <template #header-extra>
      <!-- Icon Row -->
      <div class="flex items-center gap-4 mt-2">
        <div
          v-if="customer.phone"
          class="flex items-center justify-center size-8 rounded bg-muted hover:bg-muted/80 transition-colors cursor-pointer w-full"
          title="Telefoonnummer"
        >
          <PhoneIcon class="size-4 text-muted-foreground" />
        </div>
        <div
          v-if="customer.email"
          class="flex items-center justify-center size-8 rounded bg-muted hover:bg-muted/80 transition-colors cursor-pointer w-full"
          title="E-mailadres"
        >
          <MailIcon class="size-4 text-muted-foreground" />
        </div>
      </div>
    </template>

    <template #fields>
      <!-- Time Section -->
      <InfoSection title="Time">
        <InfoItem
          label="Created"
          :value="formattedCreatedAt"
          :icon="CalendarIcon"
        />
        <InfoItem
          label="Updated"
          :value="formattedUpdatedAt"
          :icon="CalendarIcon"
        />
      </InfoSection>

      <!-- Customer Info Section -->
      <InfoSection title="Customer Info">
        <InfoItem
          label="Status"
          :value="customer.status"
          :icon="CircleIcon"
        >
          <template #value>
            <Badge
              v-if="customer.status"
              :variant="getStatusVariant(customer.status)"
              :class="getStatusInfo(customer.status)?.color || ''"
            >
              {{ getStatusInfo(customer.status)?.label || customer.status }}
            </Badge>
            <span v-else class="text-sm font-medium">Unknown</span>
          </template>
        </InfoItem>
        <InfoItem
          label="Type"
          :value="customerTypeLabel"
          :icon="customer.type === 'business' ? Building2Icon : UserIcon"
        />
        <InfoItem
          label="Name"
          :value="customer.name"
          :icon="UserIcon"
        />
        <InfoItem
          label="Customer Number"
          :value="customer.number"
          :icon="CircleIcon"
        />
        <InfoItem
          v-if="customer.email"
          label="Email"
          :value="customer.email"
          :icon="MailIcon"
        />
        <InfoItem
          v-if="customer.phone"
          label="Phone"
          :value="customer.phone"
          :icon="PhoneIcon"
        />
      </InfoSection>

      <!-- Address Section -->
      <InfoSection
        v-if="formattedAddress.length > 0"
        title="Address"
      >
        <div class="space-y-0.5">
          <div
            v-for="(line, index) in formattedAddress"
            :key="index"
            class="text-sm"
          >
            {{ line }}
          </div>
        </div>
      </InfoSection>

      <!-- Business Info Section -->
      <InfoSection
        v-if="customer.type === 'business' && (customer.kvk_number || customer.vat_number || customer.iban_number)"
        title="Business Information"
      >
        <InfoItem
          v-if="customer.kvk_number"
          label="KVK Number"
          :value="customer.kvk_number"
          :icon="Building2Icon"
        />
        <InfoItem
          v-if="customer.vat_number"
          label="VAT Number"
          :value="customer.vat_number"
          :icon="Building2Icon"
        >
          <template #value>
            <div class="flex items-center gap-1.5">
              <span class="text-sm font-medium">{{ customer.vat_number }}</span>
              <CheckCircle2Icon class="size-3.5 text-green-600 dark:text-green-500" />
            </div>
          </template>
        </InfoItem>
        <InfoItem
          v-if="customer.iban_number"
          label="IBAN Number"
          :value="customer.iban_number"
          :icon="Building2Icon"
        />
      </InfoSection>
    </template>

    <template #related-items-extra>
      <Button
        variant="outline"
        size="sm"
        class="w-full mt-2 border-dashed h-8 text-xs"
        @click="$emit('addContact')"
      >
        <PlusIcon class="size-3 mr-1.5" />
        Contactpersoon toevoegen
      </Button>
    </template>
  </DetailsSidebar>
</template>
