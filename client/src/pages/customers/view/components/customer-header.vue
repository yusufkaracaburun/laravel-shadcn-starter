<script setup lang="ts">
import type { ICustomer } from '@/pages/customers/models/customers'

import Badge from '@/components/ui/badge/Badge.vue'
import { Building2Icon, MailIcon, PhoneIcon, UserIcon } from '@/composables/use-icons.composable'

interface Props {
  customer: ICustomer
  initials: string
}

const props = defineProps<Props>()

const customerTypeLabel = computed(() => {
  return props.customer.type === 'business' ? 'Business' : 'Private'
})
</script>

<template>
  <div class="flex items-start gap-6">
    <div
      class="flex size-20 items-center justify-center rounded-full bg-primary/10 ring-4 ring-background shadow-lg"
    >
      <Building2Icon
        v-if="customer.type === 'business'"
        class="size-10 text-primary"
      />
      <UserIcon v-else class="size-10 text-primary" />
    </div>
    <div class="flex-1 min-w-0">
      <h1 class="text-3xl font-bold tracking-tight mb-2">
        {{ customer.name }}
      </h1>
      <div class="flex items-center gap-3 mb-4 flex-wrap">
        <div
          v-if="customer.email"
          class="flex items-center gap-2 text-muted-foreground"
        >
          <MailIcon class="size-4" />
          <span class="text-base">{{ customer.email }}</span>
        </div>
        <div
          v-if="customer.phone"
          class="flex items-center gap-2 text-muted-foreground"
        >
          <PhoneIcon class="size-4" />
          <span class="text-base">{{ customer.phone }}</span>
        </div>
        <Badge
          :variant="customer.type === 'business' ? 'default' : 'secondary'"
          class="shrink-0"
        >
          {{ customerTypeLabel }}
        </Badge>
      </div>
    </div>
  </div>
</template>
