<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import DetailsLayout from '@/components/global-layout/details-layout.vue'
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  FileTextIcon,
  LayoutGridIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { useVehicles } from '@/pages/vehicles/composables/use-vehicles.composable'

import VehicleDetailsTab from './components/vehicle-details-tab.vue'
import VehicleDocumentsTab from './components/vehicle-documents-tab.vue'
import VehicleDriversTab from './components/vehicle-drivers-tab.vue'
import VehicleNavbar from './components/vehicle-navbar.vue'
import VehicleOverviewTab from './components/vehicle-overview-tab.vue'
import VehicleSidebar from './components/vehicle-sidebar.vue'

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
const activeTab = ref<'overview' | 'details' | 'drivers' | 'documents'>('overview')

// Event handlers
function handleEditClosed() {
  fetchVehicleByIdData(vehicleIncludes)
}

function handleDeleteClosed() {
  // Vehicle will be redirected by the navbar component
}
</script>

<template>
  <DetailsLayout
    :title="pageTitle"
    :description="pageDescription"
    :is-loading="isLoadingVehicleById"
    :is-error="isErrorVehicleById"
    :error-object="errorVehicleById"
    :on-retry="() => fetchVehicleByIdData(vehicleIncludes)"
    error-entity-name="Vehicle"
  >
    <template #actions>
      <VehicleNavbar
        v-if="vehicle"
        :vehicle="vehicle"
        :active-tab="activeTab"
        @edit-closed="handleEditClosed"
        @delete-closed="handleDeleteClosed"
      />
    </template>

    <template v-if="vehicle" #sidebar>
            <VehicleSidebar
              :vehicle="vehicle"
              @navigate-to-tab="activeTab = $event"
            />
    </template>

    <template v-if="vehicle" #tabs>
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
                        value="drivers"
                        class="bg-background data-[state=active]:border-primary dark:data-[state=active]:border-primary h-full rounded-none border-0 border-b-2 border-transparent data-[state=active]:shadow-none"
                      >
                        <UsersIcon class="size-3.5" />
                        <span>Drivers</span>
                      </TabsTrigger>
                      <TabsTrigger
                        value="documents"
                        class="bg-background data-[state=active]:border-primary dark:data-[state=active]:border-primary h-full rounded-none border-0 border-b-2 border-transparent data-[state=active]:shadow-none"
                      >
                        <FileTextIcon class="size-3.5" />
                        <span>Documents</span>
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

                  <TabsContent value="drivers" class="mt-0">
                    <VehicleDriversTab :vehicle="vehicle" />
                  </TabsContent>

                  <TabsContent value="documents" class="mt-0">
                    <VehicleDocumentsTab :vehicle="vehicle" />
                  </TabsContent>
                </div>
              </Tabs>
        </div>
      </template>
  </DetailsLayout>
</template>
