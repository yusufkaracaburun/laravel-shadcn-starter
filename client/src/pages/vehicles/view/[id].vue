<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import Page from '@/components/global-layout/basic-page.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  FileTextIcon,
  LayoutGridIcon,
} from '@/composables/use-icons.composable'
import { useVehicles } from '@/pages/vehicles/composables/use-vehicles.composable'

import VehicleDetailsTab from './components/vehicle-details-tab.vue'
import VehicleNavbar from './components/vehicle-navbar.vue'
import VehicleOverviewTab from './components/vehicle-overview-tab.vue'
import VehicleSidebar from './components/vehicle-sidebar.vue'
import VehicleViewLayout from './components/vehicle-view-layout.vue'

// Composables
const {
  vehicleByIdResponse,
  isLoadingVehicleById,
  isErrorVehicleById,
  errorVehicleById,
  fetchVehicleByIdData,
} = useVehicles()

// Define which relations to include when fetching vehicle data
const vehicleIncludes = ['drivers']

// Fetch vehicle data with includes on mount
onMounted(() => {
  fetchVehicleByIdData(vehicleIncludes)
})

// Computed properties
const vehicle = computed<IVehicle | null>(() => {
  const data = vehicleByIdResponse.value?.data
  if (Array.isArray(data)) {
    return data[0] ?? null
  }
  return data ?? null
})

const pageTitle = computed(() => vehicle.value?.license_plate ?? 'Vehicle Details')

const pageDescription = computed(() =>
  vehicle.value ?
    `View details for ${vehicle.value.license_plate}` :
    'Loading vehicle information...',
)

// Tab state
const activeTab = ref('overview')

// Event handlers
function handleEditClosed() {
  fetchVehicleByIdData(vehicleIncludes)
}

function handleDeleteClosed() {
  // Vehicle will be redirected by the navbar component
}
</script>

<template>
  <Page :title="pageTitle" :description="pageDescription">
    <template #actions>
      <VehicleNavbar
        v-if="vehicle"
        :vehicle="vehicle"
        @edit-closed="handleEditClosed"
        @delete-closed="handleDeleteClosed"
      />
    </template>

    <VehicleViewLayout
      :is-loading="isLoadingVehicleById"
      :is-error="isErrorVehicleById"
      :error-object="errorVehicleById"
      :on-retry="() => fetchVehicleByIdData(vehicleIncludes)"
    >
      <template v-if="vehicle">
        <div class="flex h-full gap-3">
          <!-- Left Sidebar -->
          <div class="shrink-0">
            <VehicleSidebar :vehicle="vehicle" />
          </div>

          <!-- Right Main Panel -->
          <div class="flex-1 flex flex-col min-w-0">
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
                    </TabsList>
                    <ScrollBar orientation="horizontal" />
                  </ScrollArea>
                </div>

                <div class="p-3">
                  <TabsContent value="overview" class="mt-0">
                    <VehicleOverviewTab :vehicle="vehicle" />
                  </TabsContent>

                  <TabsContent value="details" class="mt-0">
                    <VehicleDetailsTab :vehicle="vehicle" />
                  </TabsContent>
                </div>
              </Tabs>
            </div>
          </div>
        </div>
      </template>
    </VehicleViewLayout>
  </Page>
</template>