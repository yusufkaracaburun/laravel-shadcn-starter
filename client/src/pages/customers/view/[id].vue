<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

import type { ICustomer } from '@/pages/customers/models/customers'

import Page from '@/components/global-layout/basic-page.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  FileTextIcon,
  LayoutGridIcon,
  ListTodoIcon,
} from '@/composables/use-icons.composable'
import { useCustomers } from '@/pages/customers/composables/use-customers.composable'

import CustomerDetailsTab from './components/customer-details-tab.vue'
import CustomerNavbar from './components/customer-navbar.vue'
import CustomerOverviewTab from './components/customer-overview-tab.vue'
import CustomerSidebar from './components/customer-sidebar.vue'
import CustomerTasksTab from './components/customer-tasks-tab.vue'
import CustomerViewLayout from './components/customer-view-layout.vue'

// Composables
const {
  customerByIdResponse,
  isLoadingCustomerById,
  isErrorCustomerById,
  errorCustomerById,
  fetchCustomerByIdData,
} = useCustomers()

// Define which relations to include when fetching customer data
const customerIncludes = ['primaryContact', 'contacts', 'invoices', 'contactsCount', 'invoicesCount']

// Fetch customer data with includes on mount
onMounted(() => {
  fetchCustomerByIdData(customerIncludes)
})

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
  customer.value ?
    `View details for ${customer.value.name}` :
    'Loading customer information...',
)

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
  fetchCustomerByIdData(customerIncludes)
}

function handleDeleteClosed() {
  // Customer will be redirected by the navbar component
}

function handleAddContact() {
  // Placeholder for add contact functionality
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
      :on-retry="() => fetchCustomerByIdData(customerIncludes)"
    >
      <template v-if="customer">
        <div class="flex h-full">
          <!-- Left Sidebar -->
          <CustomerSidebar
            :customer="customer"
            @add-contact="handleAddContact"
          />

          <!-- Right Main Panel -->
          <div class="flex-1 flex flex-col min-w-0">
            <!-- Tabs and Content -->
            <div class="flex-1 overflow-auto p-6">
              <Tabs v-model="activeTab" class="w-full">
                <TabsList
                  class="h-auto w-full justify-start gap-1 bg-muted/50 p-1 mb-6"
                >
                  <TabsTrigger
                    value="overview"
                    class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                  >
                    <LayoutGridIcon class="size-4" />
                    <span>Overzicht</span>
                  </TabsTrigger>
                  <TabsTrigger
                    value="details"
                    class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                  >
                    <FileTextIcon class="size-4" />
                    <span>Details</span>
                  </TabsTrigger>
                  <TabsTrigger
                    value="tasks"
                    class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                  >
                    <ListTodoIcon class="size-4" />
                    <span>Taken en notities</span>
                  </TabsTrigger>
                </TabsList>

                <TabsContent value="overview" class="mt-0">
                  <CustomerOverviewTab :customer="customer" />
                </TabsContent>

                <TabsContent value="details" class="mt-0">
                  <CustomerDetailsTab
                    :customer="customer"
                    :created-at="formattedCreatedAt"
                    :updated-at="formattedUpdatedAt"
                  />
                </TabsContent>

                <TabsContent value="tasks" class="mt-0">
                  <CustomerTasksTab :customer="customer" />
                </TabsContent>
              </Tabs>
            </div>
          </div>
        </div>
      </template>
    </CustomerViewLayout>
  </Page>
</template>
