<script setup lang="ts">
import { BadgeCheck } from 'lucide-vue-next'

import type { ICustomer } from '@/pages/customers/models/customers'

import DetailsSidebar from '@/components/global-layout/components/details-page/details-sidebar.vue'
import { Button } from '@/components/ui/button'
import {
  CheckCircle2Icon,
  MailIcon,
  PhoneIcon,
  PlusIcon,
  UserIcon,
} from '@/composables/use-icons.composable'

interface Props {
  customer: ICustomer
}

const props = defineProps<Props>()

const emits = defineEmits<{
  (e: 'add-contact'): void
}>()

const contacts = computed(() => {
  return props.customer.contacts ?? []
})

const formattedAddress = computed(() => {
  return props.customer.formatted_address?.multiline.split('<br/>').filter(Boolean)
})

const invoiceCount = computed(() => {
  return props.customer.invoices_count ?? 0
})

const fields = computed(() => {
  const result = []
  if (props.customer.email) {
    result.push({ label: 'E-mailadres', value: props.customer.email, icon: MailIcon })
  }
  if (props.customer.phone) {
    result.push({ label: 'Telefoonnummer', value: props.customer.phone, icon: PhoneIcon })
  }
  if (props.customer.kvk_number) {
    result.push({ label: 'KVK-nummer', value: props.customer.kvk_number })
  }
  return result
})

const relatedItems = computed(() => {
  return contacts.value.map(contact => ({
    id: contact.id,
    name: contact.name,
    icon: UserIcon,
    badge: contact.id === props.customer.primary_contact?.id ? BadgeCheck : undefined,
  }))
})
</script>

<template>
  <DetailsSidebar
    title="Customer"
    :subtitle="`Klantnummer: ${customer.number}`"
    :fields="fields"
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

    <template #fields-extra>
      <div v-if="formattedAddress.length > 0">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Adres
        </div>
        <div class="text-sm space-y-0.5">
          <div v-for="(line, index) in formattedAddress" :key="index">
            {{ line }}
          </div>
        </div>
      </div>
      <div v-if="customer.vat_number">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Btw-identificatienummer
        </div>
        <div class="flex items-center gap-1.5">
          <span class="text-sm">{{ customer.vat_number }}</span>
          <CheckCircle2Icon class="size-3.5 text-green-600 dark:text-green-500" />
        </div>
      </div>
    </template>

    <template #custom-section>
      <UiSeparator class="my-2" />
      <!-- Related Documents -->
      <div>
        <div class="text-xs font-medium text-muted-foreground mb-1">
          Aantal facturen
        </div>
        <div class="text-sm text-primary hover:underline cursor-pointer">
          {{ invoiceCount }} factu{{ invoiceCount !== 1 ? 'ren' : 'ur' }}
        </div>
      </div>
    </template>

    <template #related-items-extra>
      <Button
        variant="outline"
        size="sm"
        class="w-full mt-2 border-dashed h-8 text-xs"
        @click="$emit('add-contact')"
      >
        <PlusIcon class="size-3 mr-1.5" />
        Contactpersoon toevoegen
      </Button>
    </template>
  </DetailsSidebar>
</template>
