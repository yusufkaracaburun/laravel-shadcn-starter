<script setup lang="ts">
import type {
  IVehicle,
  IVehiclePrerequisites,
} from '@/pages/vehicles/models/vehicles'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { useAxios } from '@/composables/use-axios.composable'
import { SearchIcon } from '@/composables/use-icons.composable'
import { useVehicles } from '@/pages/vehicles/composables/use-vehicles.composable'

const props = defineProps<{
  vehicle: IVehicle
}>()

const { axiosInstance } = useAxios()
const { assignDriversToVehicle, isAssigningDrivers } = useVehicles()

const isAssignDriversOpen = ref(false)
const searchTerm = ref('')
const selectedDriverIds = ref<number[]>([])

function resetSelectedDrivers() {
  selectedDriverIds.value =
    props.vehicle.drivers?.map((driver) => driver.id) ?? []
}

const vehiclePrerequisites = ref<IVehiclePrerequisites | null>(null)
const isLoadingVehicleDrivers = ref(false)

const filteredDrivers = computed(() => {
  const drivers = vehiclePrerequisites.value?.drivers ?? []
  if (!drivers.length) {
    return []
  }

  if (!searchTerm.value.trim()) {
    return drivers
  }

  const query = searchTerm.value.trim().toLowerCase()
  return drivers.filter((driver) => {
    const name = driver.name?.toLowerCase() || ''
    const email = driver.email?.toLowerCase() || ''
    return name.includes(query) || email.includes(query)
  })
})

watch(
  () => isAssignDriversOpen.value,
  (isOpen) => {
    if (isOpen) {
      resetSelectedDrivers()
    } else {
      searchTerm.value = ''
    }
  },
)

// Reset selected drivers when vehicle changes
watch(
  () => props.vehicle.drivers,
  () => {
    if (!isAssignDriversOpen.value) {
      resetSelectedDrivers()
    }
  },
  { deep: true },
)

function getInitials(name: string): string {
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name[0]?.toUpperCase() || ''
}

function toggleDriver(id: number, checked: boolean) {
  const set = new Set(selectedDriverIds.value)
  if (checked) {
    set.add(id)
  } else {
    set.delete(id)
  }
  selectedDriverIds.value = Array.from(set)
}

async function ensureVehiclePrerequisitesLoaded() {
  if (vehiclePrerequisites.value || isLoadingVehicleDrivers.value) {
    return
  }

  try {
    isLoadingVehicleDrivers.value = true
    const response = await axiosInstance.get('/api/vehicles/prerequisites')
    vehiclePrerequisites.value = response.data?.data ?? response.data
  } catch (error) {
    console.error(error)
  } finally {
    isLoadingVehicleDrivers.value = false
  }
}

async function openAssignDriversDialog() {
  await ensureVehiclePrerequisitesLoaded()
  isAssignDriversOpen.value = true
}

async function handleAssignDriversSubmit() {
  await assignDriversToVehicle(props.vehicle.id, selectedDriverIds.value)
  isAssignDriversOpen.value = false
}
</script>

<template>
  <div class="space-y-3">
    <div class="flex items-center justify-between">
      <p class="text-xs text-muted-foreground">
        Assign drivers to this vehicle.
      </p>
      <UiButton
        size="sm"
        variant="outline"
        class="h-7 px-2 text-xs"
        @click="openAssignDriversDialog"
      >
        Assign drivers
      </UiButton>
    </div>

    <div class="space-y-2">
      <div
        v-for="driver in vehicle.drivers"
        :key="driver.id"
        class="flex items-center gap-2"
      >
        <Avatar class="size-8">
          <AvatarImage
            v-if="driver.profile_photo_url"
            :src="driver.profile_photo_url"
            :alt="driver.name"
          />
          <AvatarFallback>
            {{ getInitials(driver.name) }}
          </AvatarFallback>
        </Avatar>
        <div class="flex-1">
          <div class="text-sm font-medium">
            {{ driver.name }}
          </div>
          <div class="text-xs text-muted-foreground">
            {{ driver.email }}
          </div>
        </div>
      </div>
    </div>

    <!-- Assign Drivers Dialog -->
    <UiDialog v-model:open="isAssignDriversOpen">
      <UiDialogContent class="sm:max-w-md">
        <UiDialogHeader>
          <UiDialogTitle>Assign drivers</UiDialogTitle>
          <UiDialogDescription>
            Select one or more drivers to associate with this vehicle.
          </UiDialogDescription>
        </UiDialogHeader>

        <div class="mt-2 space-y-3">
          <UiSeparator />

          <div
            v-if="isLoadingVehicleDrivers"
            class="flex items-center justify-center py-6"
          >
            <UiSpinner class="h-4 w-4 text-muted-foreground" />
          </div>
          <template v-else-if="vehiclePrerequisites?.drivers?.length">
            <div class="relative">
              <SearchIcon
                class="absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
              />
              <UiInput
                v-model="searchTerm"
                placeholder="Search drivers by name or email..."
                class="pl-8"
              />
            </div>

            <div
              v-if="!filteredDrivers.length"
              class="py-4 text-xs text-muted-foreground text-center"
            >
              No drivers found matching "{{ searchTerm }}".
            </div>
            <div
              v-else
              class="max-h-100 overflow-y-auto space-y-2 text-sm pr-2"
            >
              <label
                v-for="driver in filteredDrivers"
                :key="driver.id"
                class="flex items-center gap-2"
              >
                <UiCheckbox
                  :checked="selectedDriverIds.includes(driver.id)"
                  @update:checked="toggleDriver(driver.id, $event)"
                />
                <span>
                  {{ driver.name }}
                  <span class="text-xs text-muted-foreground">
                    ({{ driver.email }})
                  </span>
                </span>
              </label>
            </div>
          </template>
          <div v-else class="py-4 text-xs text-muted-foreground">
            No drivers available to assign.
          </div>
        </div>

        <UiDialogFooter class="mt-4">
          <UiButton
            type="button"
            variant="outline"
            :disabled="isAssigningDrivers"
            @click="isAssignDriversOpen = false"
          >
            Cancel
          </UiButton>
          <UiButton
            type="button"
            :loading="isAssigningDrivers"
            :disabled="isAssigningDrivers"
            @click="handleAssignDriversSubmit"
          >
            Save
          </UiButton>
        </UiDialogFooter>
      </UiDialogContent>
    </UiDialog>
  </div>
</template>
