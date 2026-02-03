<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

import type { ICustomer } from '@/pages/customers/models/customers'

import DetailsLayout from '@/components/global-layout/details-layout.vue'
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area'
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
  <DetailsLayout
    :title="pageTitle"
    :description="pageDescription"
    :is-loading="isLoadingCustomerById"
    :is-error="isErrorCustomerById"
    :error-object="errorCustomerById"
    :on-retry="() => fetchCustomerByIdData(customerIncludes)"
    error-entity-name="Customer"
  >
    <template #actions>
      <CustomerNavbar
        v-if="customer"
        :customer="customer"
        @edit-closed="handleEditClosed"
        @delete-closed="handleDeleteClosed"
      />
    </template>

    <template v-if="customer" #sidebar>
            <CustomerSidebar
              :customer="customer"
              @add-contact="handleAddContact"
            />
    </template>

    <template v-if="customer" #tabs>
            <!-- Tabs and Content -->
            <div class="flex-1 overflow-auto">
              <Tabs v-model="activeTab" default-value="overview" class-name="gap-4">
                <div class="sticky top-0 z-10 bg-background border-b">
                  <ScrollArea>
                    <TabsList
                      class="bg-background rounded-none border-b p-0"
                    >
                      <TabsTrigger
                        value="overview"
                        class="bg-background data-[state=active]:border-primary dark:data-[state=active]:border-primary h-full rounded-none border-0 border-b-2 border-transparent data-[state=active]:shadow-none"
                      >
                        <LayoutGridIcon class="size-3.5" />
                        <span>Overzicht</span>
                      </TabsTrigger>
                      <TabsTrigger
                        value="details"
                        class="bg-background data-[state=active]:border-primary dark:data-[state=active]:border-primary h-full rounded-none border-0 border-b-2 border-transparent data-[state=active]:shadow-none"
                      >
                        <FileTextIcon class="size-3.5" />
                        <span>Details</span>
                      </TabsTrigger>
                      <TabsTrigger
                        value="tasks"
                        class="bg-background data-[state=active]:border-primary dark:data-[state=active]:border-primary h-full rounded-none border-0 border-b-2 border-transparent data-[state=active]:shadow-none"
                      >
                        <ListTodoIcon class="size-3.5" />
                        <span>Taken en notities</span>
                      </TabsTrigger>
                    </TabsList>
                    <ScrollBar orientation="horizontal" />
                  </ScrollArea>
                </div>

                <div class="p-3">
                  <TabsContent value="overview" class="mt-0">
                    <CustomerOverviewTab :customer="customer" />
                  </TabsContent>

                  <TabsContent value="details" class="mt-0">
                    <CustomerDetailsTab
                      :customer="customer"
                    />
                  </TabsContent>

                  <TabsContent value="tasks" class="mt-0">
                    <CustomerTasksTab :customer="customer" />
                  </TabsContent>
                </div>
              </Tabs>
        </div>
      </template>
  </DetailsLayout>
</template>
