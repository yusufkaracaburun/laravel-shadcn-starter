<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import {
  ArrowLeft,
  Building2,
  FilePenLine,
  Mail,
  MapPin,
  Phone,
  Trash2,
  User,
} from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { useGetCustomerQuery } from '@/services/customers.service'

import type { Customer } from './data/schema'

import CustomerDelete from './components/customer-delete.vue'
import CustomerResourceDialog from './components/customer-resource-dialog.vue'

const route = useRoute()
const router = useRouter()

const customerId = computed(() => Number(route.params.id))

const {
  data: customerResponse,
  isLoading,
  isError,
  error,
  refetch,
} = useGetCustomerQuery(customerId)

const customer = computed<Customer | null>(() => customerResponse.value?.data ?? null)

const showComponent = shallowRef<typeof CustomerResourceDialog | typeof CustomerDelete | null>(null)
const isDialogOpen = ref(false)

type TCommand = 'edit' | 'delete'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      showComponent.value = CustomerResourceDialog
      isDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = CustomerDelete
      isDialogOpen.value = true
      break
  }
}

function handleEditClose() {
  isDialogOpen.value = false
  // Refetch customer data after edit
  refetch()
}

function handleDeleteClose() {
  isDialogOpen.value = false
  // Navigate back to customers list after deletion
  router.push('/customers')
}

// Format date from "d-m-Y H:i:s" format
function formatDateTime(dateString: string | null): string {
  if (!dateString)
    return '—'
  const [datePart, timePart] = dateString.split(' ')
  const [day, month, year] = datePart.split('-')
  const date = new Date(`${year}-${month}-${day} ${timePart}`)
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <Page title="Customer Details" description="View and manage customer information" sticky>
    <template #actions>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="router.back()">
          <ArrowLeft class="mr-2 size-4" />
          Back
        </Button>
        <Button v-if="customer" variant="outline" size="sm" @click="handleSelect('edit')">
          <FilePenLine class="mr-2 size-4" />
          Edit
        </Button>
        <Button v-if="customer" variant="destructive" size="sm" @click="handleSelect('delete')">
          <Trash2 class="mr-2 size-4" />
          Delete
        </Button>
      </div>
    </template>

    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <Loading />
    </div>

    <Error
      v-else-if="isError"
      :error="error"
      :retry="refetch"
      title="Failed to load customer"
      description="We couldn't load the customer details. Please try again."
    />

    <div v-else-if="customer" class="space-y-6">
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <User class="size-5 text-muted-foreground" />
              <CardTitle>{{ customer.name }}</CardTitle>
            </div>
            <Badge :variant="customer.type === 'business' ? 'default' : 'secondary'">
              {{ customer.type === 'business' ? 'Business' : 'Private' }}
            </Badge>
          </div>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2">
            <div v-if="customer.email" class="flex items-center gap-2">
              <Mail class="size-4 text-muted-foreground" />
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  Email
                </p>
                <p class="text-sm">
                  {{ customer.email }}
                </p>
              </div>
            </div>
            <div v-if="customer.phone" class="flex items-center gap-2">
              <Phone class="size-4 text-muted-foreground" />
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  Phone
                </p>
                <p class="text-sm">
                  {{ customer.phone }}
                </p>
              </div>
            </div>
            <div v-if="customer.city || customer.address" class="flex items-center gap-2">
              <MapPin class="size-4 text-muted-foreground" />
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  Address
                </p>
                <p class="text-sm">
                  <span v-if="customer.address">{{ customer.address }}</span>
                  <span v-if="customer.address && customer.zipcode">, </span>
                  <span v-if="customer.zipcode">{{ customer.zipcode }}</span>
                  <span v-if="(customer.address || customer.zipcode) && customer.city" />
                  <span v-if="customer.city">{{ customer.city }}</span>
                  <span
                    v-if="
                      customer.country && (customer.city || customer.zipcode || customer.address)
                    "
                  >,
                  </span>
                  <span v-if="customer.country">{{ customer.country }}</span>
                </p>
              </div>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                Customer ID
              </p>
              <p class="text-sm font-semibold">
                #{{ customer.id }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card v-if="customer.type === 'business'">
        <CardHeader>
          <div class="flex items-center gap-2">
            <Building2 class="size-5 text-muted-foreground" />
            <CardTitle>Business Information</CardTitle>
          </div>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2">
            <div v-if="customer.kvk_number">
              <p class="text-sm font-medium text-muted-foreground">
                KVK Number
              </p>
              <p class="text-sm font-semibold">
                {{ customer.kvk_number }}
              </p>
            </div>
            <div v-if="customer.vat_number">
              <p class="text-sm font-medium text-muted-foreground">
                VAT Number
              </p>
              <p class="text-sm font-semibold">
                {{ customer.vat_number }}
              </p>
            </div>
            <div v-if="customer.iban_number">
              <p class="text-sm font-medium text-muted-foreground">
                IBAN Number
              </p>
              <p class="text-sm font-semibold">
                {{ customer.iban_number }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card v-if="customer.primary_contact">
        <CardHeader>
          <CardTitle>Primary Contact</CardTitle>
          <CardDescription v-if="customer.primary_contact">
            Main contact person for this customer
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                Name
              </p>
              <p class="text-sm font-semibold">
                {{ customer.primary_contact.name }}
              </p>
            </div>
            <div v-if="customer.primary_contact.email" class="grid gap-4 md:grid-cols-2">
              <div>
                <p class="text-sm font-medium text-muted-foreground">
                  Email
                </p>
                <p class="text-sm">
                  {{ customer.primary_contact.email }}
                </p>
              </div>
              <div v-if="customer.primary_contact.phone">
                <p class="text-sm font-medium text-muted-foreground">
                  Phone
                </p>
                <p class="text-sm">
                  {{ customer.primary_contact.phone }}
                </p>
              </div>
            </div>
            <div v-if="customer.primary_contact.user" class="border-t pt-4">
              <p class="text-sm font-medium text-muted-foreground mb-2">
                Associated User
              </p>
              <div class="flex items-center gap-3">
                <div
                  v-if="customer.primary_contact.user.profile_photo_url"
                  class="size-10 rounded-full overflow-hidden"
                >
                  <img
                    :src="customer.primary_contact.user.profile_photo_url"
                    :alt="customer.primary_contact.user.name"
                    class="w-full h-full object-cover"
                  >
                </div>
                <div>
                  <p class="text-sm font-semibold">
                    {{ customer.primary_contact.user.name }}
                  </p>
                  <p class="text-xs text-muted-foreground">
                    {{ customer.primary_contact.user.email }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card v-if="customer.contacts && customer.contacts.length > 0">
        <CardHeader>
          <CardTitle>All Contacts</CardTitle>
          <CardDescription>
            {{ customer.contacts.length }} contact(s) associated with this customer
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div
              v-for="contact in customer.contacts"
              :key="contact.id"
              class="border-b pb-4 last:border-0 last:pb-0"
            >
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-semibold">
                    {{ contact.name }}
                  </p>
                  <p v-if="contact.email" class="text-xs text-muted-foreground">
                    {{ contact.email }}
                  </p>
                </div>
                <Badge v-if="contact.id === customer.primary_contact?.id" variant="default">
                  Primary
                </Badge>
              </div>
              <div v-if="contact.user" class="mt-2 flex items-center gap-2">
                <div
                  v-if="contact.user.profile_photo_url"
                  class="size-6 rounded-full overflow-hidden"
                >
                  <img
                    :src="contact.user.profile_photo_url"
                    :alt="contact.user.name"
                    class="w-full h-full object-cover"
                  >
                </div>
                <p class="text-xs text-muted-foreground">
                  {{ contact.user.name }}
                </p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Timestamps</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                Created At
              </p>
              <p class="text-sm">
                {{ formatDateTime(customer.created_at) }}
              </p>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">
                Updated At
              </p>
              <p class="text-sm">
                {{ formatDateTime(customer.updated_at) }}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <UiDialog v-model:open="isDialogOpen">
      <UiDialogContent v-if="showComponent && customer" class="sm:max-w-[425px]">
        <CustomerResourceDialog
          v-if="showComponent === CustomerResourceDialog"
          :customer="customer"
          @close="handleEditClose"
        />
        <CustomerDelete
          v-else-if="showComponent === CustomerDelete"
          :customer="customer"
          @close="handleDeleteClose"
        />
      </UiDialogContent>
    </UiDialog>
  </Page>
</template>
