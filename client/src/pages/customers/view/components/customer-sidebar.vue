<script setup lang="ts">
import { BadgeCheck } from 'lucide-vue-next'

import type { IContact, ICustomer } from '@/pages/customers/models/customers'

import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import { Button } from '@/components/ui/button'
import {
  Building2Icon,
  CheckCircle2Icon,
  FileTextIcon,
  MailIcon,
  PencilIcon,
  PhoneIcon,
  PlusIcon,
  UserIcon,
} from '@/composables/use-icons.composable'

interface Props {
  customer: ICustomer
}

const props = defineProps<Props>()

const contacts = computed(() => {
  return props.customer.contacts ?? []
})

const formattedAddress = computed(() => {
  return props.customer.formatted_address?.multiline.split('<br/>').filter(Boolean)
})

const invoiceCount = computed(() => {
  return props.customer.invoices_count ?? 0
})
</script>

<template>
  <div class="w-80 border-r bg-background p-3 space-y-2.5 h-full overflow-y-auto">
    <!-- Header -->
    <div>
      <h2 class="text-sm font-semibold mb-1.5">
        Customer
      </h2>
      <div class="text-lg font-bold">
        {{ customer.name }}
      </div>
      <div class="text-xs text-muted-foreground mt-0.5">
        Klantnummer: {{ customer.number }}
      </div>
    </div>

    <UiSeparator class="my-2" />

    <!-- Icon Row -->
    <div class="flex items-center gap-4">
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

    <UiSeparator class="my-2" />

    <!-- Contact Fields -->
    <div class="space-y-2">
      <div v-if="customer.email">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          E-mailadres
        </div>
        <div class="text-sm">
          {{ customer.email }}
        </div>
      </div>

      <div v-if="customer.phone">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
          Telefoonnummer
        </div>
        <div class="text-sm">
          {{ customer.phone }}
        </div>
      </div>

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

      <div v-if="customer.kvk_number">
        <div class="text-xs font-medium text-muted-foreground mb-0.5">
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
        <div class="flex items-center gap-1.5">
          <span class="text-sm">{{ customer.vat_number }}</span>
          <CheckCircle2Icon class="size-3.5 text-green-600 dark:text-green-500" />
        </div>
      </div>
    </div>

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

    <UiSeparator class="my-2" />

    <!-- Contact Persons -->
    <div>
      <div class="text-xs font-medium text-muted-foreground mb-2">
        Contactpersonen
      </div>
      <div class="space-y-1">
        <div
          v-for="contact in contacts"
          :key="contact.id"
          class="flex items-center justify-between gap-2 py-1.5 hover:bg-muted/50 rounded transition-colors group"
        >
          <div class="flex items-center gap-2 flex-1 min-w-0">
            <Avatar class="size-6 shrink-0">
              <AvatarFallback class="bg-muted text-xs">
                <UserIcon class="size-3" />
              </AvatarFallback>
            </Avatar>
            <div class="flex items-center gap-2">
              <div class="text-sm truncate">
                {{ contact.name }}
              </div>
              <template v-if="contact.id === customer.primary_contact?.id">
                <BadgeCheck class="size-4 text-blue-50090 dark:text-blue-400" title="Primair" />
              </template>
            </div>
          </div>
          <Button
            variant="ghost"
            size="icon-sm"
            class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity h-6 w-6"
            title="Bewerken"
          >
            <PencilIcon class="size-3" />
          </Button>
        </div>
      </div>
      <Button
        variant="outline"
        size="sm"
        class="w-full mt-2 border-dashed h-8 text-xs"
        @click="$emit('add-contact')"
      >
        <PlusIcon class="size-3 mr-1.5" />
        Contactpersoon toevoegen
      </Button>
    </div>
  </div>
</template>
