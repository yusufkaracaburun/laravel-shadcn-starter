<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'

import type { ICustomer } from '@/pages/customers/models/customers'

import InfoItem from '@/components/global-layout/components/info-item.vue'
import InfoSection
  from '@/components/global-layout/components/info-section.vue'
import SheetDrawer
  from '@/components/global-layout/components/sheet-drawer.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import { Button } from '@/components/ui/button'
import {
  SheetDescription,
  SheetFooter,
  SheetHeader,
  SheetTitle,
} from '@/components/ui/sheet'
import {
  ArrowRightIcon,
  Building2Icon,
  CalendarIcon,
  CircleIcon,
  FileTextIcon,
  MailIcon,
  PhoneIcon,
  UserIcon,
} from '@/composables/use-icons.composable'
import { formatDate } from '@/utils/date'
import { getCustomerStatusColor } from '@/utils/status-colors'

import { statuses } from '../data/data'

const props = defineProps<{
  customer: ICustomer
  open: boolean
}>()

const emit = defineEmits<{
  'close': []
  'update:open': [value: boolean]
}>()

const router = useRouter()

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

const formattedCreatedAt = computed(() => formatDate(props.customer.created_at))
const formattedUpdatedAt = computed(() => formatDate(props.customer.updated_at))

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

const customerTypeLabel = computed(() => {
  return props.customer.type === 'business' ? 'Business' : 'Private'
})

const contacts = computed(() => {
  return props.customer.contacts ?? []
})

const invoiceCount = computed(() => {
  return props.customer.invoices_count ?? 0
})

function handleViewFullDetails() {
  router.push({
    name: '/customers/view/[id]',
    params: { id: props.customer.id.toString() },
  })
  emit('close')
}
</script>

<template>
  <SheetDrawer
    :title="customer.name"
    description="Customer details"
    :open="open"
    side="right"
    @close="emit('close')"
    @update:open="(value) => emit('update:open', value)"
  >
    <template #header>
      <SheetHeader>
        <SheetTitle>
          {{ customer.name }}
        </SheetTitle>
        <SheetDescription>
          Customer number: {{ customer.number }}
        </SheetDescription>
      </SheetHeader>
    </template>
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
      />
      <InfoItem
        v-if="customer.iban_number"
        label="IBAN Number"
        :value="customer.iban_number"
        :icon="Building2Icon"
      />
    </InfoSection>

    <!-- Contacts & Invoices Section -->
    <InfoSection
      v-if="contacts.length > 0 || invoiceCount > 0"
      title="Contacts & Invoices"
    >
      <div v-if="contacts.length > 0" class="space-y-2">
        <div class="text-xs font-medium text-muted-foreground mb-1">
          Contacts ({{ contacts.length }})
        </div>
        <div class="space-y-1">
          <div
            v-for="contact in contacts"
            :key="contact.id"
            class="flex items-center gap-2 text-sm"
          >
            <UserIcon class="size-4 text-muted-foreground" />
            <span>{{ contact.name }}</span>
            <Badge
              v-if="contact.id === customer.primary_contact?.id"
              variant="secondary"
              class="text-xs"
            >
              Primary
            </Badge>
          </div>
        </div>
      </div>
      <div v-if="invoiceCount > 0" class="mt-3">
        <div class="text-xs font-medium text-muted-foreground mb-1">
          Invoices
        </div>
        <div class="flex items-center gap-2 text-sm">
          <FileTextIcon class="size-4 text-muted-foreground" />
          <span>{{ invoiceCount }} invoice{{ invoiceCount !== 1 ? 's' : '' }}</span>
        </div>
      </div>
    </InfoSection>

    <template #footer>
      <SheetFooter>
        <Button
          variant="outline"
          size="sm"
          class="gap-2"
          @click="handleViewFullDetails"
        >
          <ArrowRightIcon class="size-4" />
          View Full Details
        </Button>
      </SheetFooter>
    </template>
  </SheetDrawer>
</template>
