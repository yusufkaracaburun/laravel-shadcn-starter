<script setup lang="ts">
import type { ICustomer } from '@/pages/customers/models/customers'

import { Avatar, AvatarFallback } from '@/components/ui/avatar'
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

const invoiceCount = computed(() => {
  return props.customer.invoices_count ?? 0
})
</script>

<template>
  <div class="w-80 border-r bg-muted/30 p-6 space-y-6">
    <!-- Header -->
    <div>
      <h2 class="text-lg font-semibold mb-2">Contact</h2>
      <div class="text-xl font-bold">{{ customer.name }}</div>
      <div class="text-sm text-muted-foreground mt-1">
        Klantnummer: {{ customer.number }}
      </div>
    </div>

    <!-- Icon Row -->
    <div class="flex items-center gap-3">
      <div
        v-if="customer.phone"
        class="flex items-center justify-center size-10 rounded-md bg-muted hover:bg-muted/80 transition-colors cursor-pointer"
        title="Telefoonnummer"
      >
        <PhoneIcon class="size-5 text-muted-foreground" />
      </div>
      <div
        v-if="customer.email"
        class="flex items-center justify-center size-10 rounded-md bg-muted hover:bg-muted/80 transition-colors cursor-pointer"
        title="E-mailadres"
      >
        <MailIcon class="size-5 text-muted-foreground" />
      </div>
      <div
        class="flex items-center justify-center size-10 rounded-md bg-muted hover:bg-muted/80 transition-colors cursor-pointer"
        title="Documenten"
      >
        <FileTextIcon class="size-5 text-muted-foreground" />
      </div>
      <div
        class="flex items-center justify-center size-10 rounded-md bg-muted hover:bg-muted/80 transition-colors cursor-pointer"
        title="Bedrijf"
      >
        <Building2Icon class="size-5 text-muted-foreground" />
      </div>
    </div>

    <!-- Contact Fields -->
    <div class="space-y-4">
      <div v-if="customer.email">
        <div class="text-sm font-medium text-muted-foreground mb-1">
          E-mailadres
        </div>
        <div class="text-base">{{ customer.email }}</div>
      </div>

      <div v-if="customer.phone">
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Telefoonnummer
        </div>
        <div class="text-base">{{ customer.phone }}</div>
      </div>

      <div v-if="formattedAddress.length > 0">
        <div class="text-sm font-medium text-muted-foreground mb-1">Adres</div>
        <div class="text-base space-y-0.5">
          <div v-for="(line, index) in formattedAddress" :key="index">
            {{ line }}
          </div>
        </div>
      </div>

      <div v-if="customer.kvk_number">
        <div class="text-sm font-medium text-muted-foreground mb-1">
          KVK-nummer
        </div>
        <div class="text-base">{{ customer.kvk_number }}</div>
      </div>

      <div v-if="customer.vat_number">
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Btw-identificatienummer
        </div>
        <div class="flex items-center gap-2">
          <span class="text-base">{{ customer.vat_number }}</span>
          <CheckCircle2Icon class="size-4 text-green-600" />
        </div>
      </div>
    </div>

    <!-- Related Documents -->
    <div>
      <div class="text-sm font-medium text-muted-foreground mb-2">
        Bijbehorende documenten
      </div>
      <div class="text-base text-primary hover:underline cursor-pointer">
        {{ invoiceCount }} Externe factuur{{ invoiceCount !== 1 ? 'en' : '' }}
      </div>
    </div>

    <!-- Contact Persons -->
    <div>
      <div class="text-sm font-medium text-muted-foreground mb-3">
        Contactpersonen
      </div>
      <div class="space-y-3">
        <div
          v-for="contact in contacts"
          :key="contact.id"
          class="flex items-start justify-between gap-2"
        >
          <div class="flex items-start gap-2 flex-1 min-w-0">
            <Avatar class="size-8 shrink-0">
              <AvatarFallback>
                <UserIcon class="size-4" />
              </AvatarFallback>
            </Avatar>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-medium truncate">
                {{ contact.name }}
              </div>
            </div>
          </div>
          <Button
            variant="ghost"
            size="icon-sm"
            class="shrink-0"
            title="Bewerken"
          >
            <PencilIcon class="size-4" />
          </Button>
        </div>
      </div>
      <Button
        variant="outline"
        class="w-full mt-3"
        @click="$emit('add-contact')"
      >
        <PlusIcon class="size-4 mr-2" />
        Contactpersoon toevoegen
      </Button>
    </div>
  </div>
</template>
