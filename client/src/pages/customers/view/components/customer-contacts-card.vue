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
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { MailIcon, PhoneIcon, UserIcon } from '@/composables/use-icons.composable'

interface Props {
  customer: ICustomer
}

const props = defineProps<Props>()

const contacts = computed(() => {
  return props.customer.contacts ?? []
})

const primaryContact = computed(() => {
  return props.customer.primary_contact
})
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Contacts</CardTitle>
      <CardDescription>
        Contact persons associated with this customer
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-6">
      <!-- Primary Contact -->
      <div v-if="primaryContact">
        <div class="mb-4">
          <h3 class="text-sm font-semibold mb-2">Primary Contact</h3>
          <div class="space-y-3 p-4 rounded-lg border bg-muted/30">
            <div class="flex items-start justify-between">
              <div class="flex items-start gap-3 flex-1">
                <div
                  v-if="primaryContact.user?.profile_photo_url"
                  class="size-10 rounded-full overflow-hidden shrink-0"
                >
                  <img
                    :src="primaryContact.user.profile_photo_url"
                    :alt="primaryContact.name"
                    class="w-full h-full object-cover"
                  >
                </div>
                <Avatar
                  v-else
                  class="size-10 shrink-0"
                >
                  <AvatarFallback>
                    <UserIcon class="size-5" />
                  </AvatarFallback>
                </Avatar>
                <div class="flex-1 min-w-0">
                  <div class="font-semibold">
                    {{ primaryContact.name }}
                  </div>
                  <div
                    v-if="primaryContact.email"
                    class="flex items-center gap-1 text-sm text-muted-foreground mt-1"
                  >
                    <MailIcon class="size-3" />
                    {{ primaryContact.email }}
                  </div>
                  <div
                    v-if="primaryContact.phone"
                    class="flex items-center gap-1 text-sm text-muted-foreground mt-1"
                  >
                    <PhoneIcon class="size-3" />
                    {{ primaryContact.phone }}
                  </div>
                  <div
                    v-if="primaryContact.user"
                    class="mt-2 pt-2 border-t"
                  >
                    <div class="text-xs text-muted-foreground">
                      Associated User
                    </div>
                    <div class="text-sm font-medium mt-1">
                      {{ primaryContact.user.name }}
                    </div>
                    <div class="text-xs text-muted-foreground">
                      {{ primaryContact.user.email }}
                    </div>
                  </div>
                </div>
              </div>
              <Badge variant="default" class="shrink-0">
                Primary
              </Badge>
            </div>
          </div>
        </div>
      </div>

      <!-- All Contacts -->
      <div v-if="contacts.length > 0">
        <h3 class="text-sm font-semibold mb-3">
          All Contacts ({{ contacts.length }})
        </h3>
        <div class="space-y-3">
          <div
            v-for="contact in contacts"
            :key="contact.id"
            class="p-3 rounded-lg border hover:bg-muted/50 transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex items-start gap-3 flex-1">
                <div
                  v-if="contact.user?.profile_photo_url"
                  class="size-8 rounded-full overflow-hidden shrink-0"
                >
                  <img
                    :src="contact.user.profile_photo_url"
                    :alt="contact.name"
                    class="w-full h-full object-cover"
                  >
                </div>
                <Avatar
                  v-else
                  class="size-8 shrink-0"
                >
                  <AvatarFallback>
                    <UserIcon class="size-4" />
                  </AvatarFallback>
                </Avatar>
                <div class="flex-1 min-w-0">
                  <div class="font-medium">
                    {{ contact.name }}
                  </div>
                  <div
                    v-if="contact.email"
                    class="text-xs text-muted-foreground mt-1"
                  >
                    {{ contact.email }}
                  </div>
                  <div
                    v-if="contact.phone"
                    class="text-xs text-muted-foreground mt-1"
                  >
                    {{ contact.phone }}
                  </div>
                  <div
                    v-if="contact.user"
                    class="mt-2 flex items-center gap-2"
                  >
                    <div
                      v-if="contact.user.profile_photo_url"
                      class="size-5 rounded-full overflow-hidden"
                    >
                      <img
                        :src="contact.user.profile_photo_url"
                        :alt="contact.user.name"
                        class="w-full h-full object-cover"
                      >
                    </div>
                    <span class="text-xs text-muted-foreground">
                      {{ contact.user.name }}
                    </span>
                  </div>
                </div>
              </div>
              <Badge
                v-if="contact.id === primaryContact?.id"
                variant="default"
                class="shrink-0"
              >
                Primary
              </Badge>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="!primaryContact && contacts.length === 0"
        class="text-center py-8"
      >
        <p class="text-sm text-muted-foreground">
          No contacts found.
        </p>
      </div>
    </CardContent>
  </Card>
</template>
