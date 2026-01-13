<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, ref } from 'vue'

import type { ICustomer } from '@/pages/customers/models/customers'

import Page from '@/components/global-layout/basic-page.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  FileTextIcon,
  LayoutGridIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { useCustomers } from '@/pages/customers/composables/use-customers.composable'

import CustomerAccountStatusCard from './components/customer-account-status-card.vue'
import CustomerContactsCard from './components/customer-contacts-card.vue'
import CustomerHeader from './components/customer-header.vue'
import CustomerInvoicesCard from './components/customer-invoices-card.vue'
import CustomerNavbar from './components/customer-navbar.vue'
import CustomerProfileCard from './components/customer-profile-card.vue'
import CustomerViewLayout from './components/customer-view-layout.vue'

// Composables
const {
  customerByIdResponse,
  isLoadingCustomerById,
  isErrorCustomerById,
  errorCustomerById,
  fetchCustomerByIdData,
} = useCustomers()

// Computed properties
const customer = computed<ICustomer | null>(() => {
  const data = customerByIdResponse.value?.data
  // Handle case where data might be an array (shouldn't happen, but type safety)
  if (Array.isArray(data)) {
    return data[0] ?? null
  }
  return data ?? null
})

const pageTitle = computed(() => customer.value?.name ?? 'Customer Details')

const pageDescription = computed(() =>
  customer.value
    ? `View details for ${customer.value.name}`
    : 'Loading customer information...',
)

// Computed values for components
const customerInitials = computed(() => {
  if (!customer.value) {
    return '?'
  }

  const name = customer.value.name
  if (!name || name === '—') {
    return '?'
  }

  const parts = name.trim().split(/\s+/)

  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }

  return name[0].toUpperCase()
})

// Format date from "d-m-Y H:i:s" format
function formatDateTime(dateString: string | null): string {
  if (!dateString) {
    return '—'
  }
  try {
    // Parse "d-m-Y H:i:s" format (e.g., "31-12-2023 14:30:25")
    const [datePart, timePart] = dateString.split(' ')
    const [day, month, year] = datePart.split('-')
    const date = new Date(`${year}-${month}-${day} ${timePart}`)
    
    if (Number.isNaN(date.getTime())) {
      return dateString
    }

    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return dateString
  }
}

const formattedCreatedAt = computed(() => {
  if (!customer.value?.created_at) {
    return '—'
  }
  return formatDateTime(customer.value.created_at)
})

const formattedUpdatedAt = computed(() => {
  if (!customer.value?.updated_at) {
    return '—'
  }
  return formatDateTime(customer.value.updated_at)
})

// Tab state
const activeTab = ref('overview')

// Event handlers
function handleEditClosed() {
  fetchCustomerByIdData()
}

function handleDeleteClosed() {
  // Customer will be redirected by the navbar component
}
</script>

<template>
  <Page :title="pageTitle" :description="pageDescription">
    <template #actions>
      <CustomerNavbar
        v-if="customer"
        :customer="customer"
        @edit-closed="handleEditClosed"
        @delete-closed="handleDeleteClosed"
      />
    </template>

    <CustomerViewLayout
      :is-loading="isLoadingCustomerById"
      :is-error="isErrorCustomerById"
      :error-object="errorCustomerById"
      :on-retry="fetchCustomerByIdData"
    >
      <template v-if="customer">
        <div class="space-y-8">
          <!-- Enhanced Header Section -->
          <div
            class="relative overflow-hidden rounded-xl border bg-gradient-to-br from-background to-muted/20 p-8 shadow-sm"
          >
            <div class="relative z-10">
              <CustomerHeader
                :customer="customer"
                :initials="customerInitials"
              />
            </div>
          </div>

          <!-- Modern Tabs Section -->
          <div class="space-y-6">
            <Tabs v-model="activeTab" class="w-full">
              <TabsList
                class="h-auto w-full justify-start gap-1 bg-muted/50 p-1"
              >
                <TabsTrigger
                  value="overview"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <LayoutGridIcon class="size-4" />
                  <span>Overview</span>
                </TabsTrigger>
                <TabsTrigger
                  value="contacts"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <UsersIcon class="size-4" />
                  <span>Contacts</span>
                </TabsTrigger>
                <TabsTrigger
                  value="invoices"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <FileTextIcon class="size-4" />
                  <span>Invoices</span>
                </TabsTrigger>
              </TabsList>

              <TabsContent value="overview" class="mt-8">
                <div class="grid gap-6 lg:grid-cols-2">
                  <CustomerProfileCard :customer="customer" />

                  <CustomerAccountStatusCard
                    :customer="customer"
                    :created-at="formattedCreatedAt"
                    :updated-at="formattedUpdatedAt"
                  />
                </div>
              </TabsContent>

              <TabsContent value="contacts" class="mt-8">
                <div class="max-w-2xl">
                  <CustomerContactsCard :customer="customer" />
                </div>
              </TabsContent>

              <TabsContent value="invoices" class="mt-8">
                <div class="max-w-2xl">
                  <CustomerInvoicesCard :customer="customer" />
                </div>
              </TabsContent>
            </Tabs>
          </div>
        </div>
      </template>
    </CustomerViewLayout>
  </Page>
</template>
