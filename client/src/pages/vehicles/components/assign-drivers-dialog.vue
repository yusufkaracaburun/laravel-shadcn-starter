<script setup lang="ts">
import type { IVehiclePrerequisites } from '@/pages/vehicles/models/vehicles'

import { SearchIcon } from '@/composables/use-icons.composable'

const props = defineProps<{
  open: boolean
  vehicleId: number
  drivers?: IVehiclePrerequisites['drivers']
  loading?: boolean
  selectedDriverIds: number[]
  assigning?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'update:selectedDriverIds', value: number[]): void
  (e: 'submit'): void
}>()

const openModel = computed({
  get: () => props.open,
  set: (value: boolean) => emit('update:open', value),
})

const searchTerm = ref('')

watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) {
      searchTerm.value = ''
    }
  },
)

const filteredDrivers = computed(() => {
  if (!props.drivers || !props.drivers.length) {
    return []
  }

  if (!searchTerm.value.trim()) {
    return props.drivers
  }

  const query = searchTerm.value.trim().toLowerCase()
  return props.drivers.filter((driver) => {
    const name = driver.name?.toLowerCase() || ''
    const email = driver.email?.toLowerCase() || ''
    return name.includes(query) || email.includes(query)
  })
})

function toggleDriver(id: number, checked: boolean) {
  const set = new Set(props.selectedDriverIds)
  if (checked) {
    set.add(id)
  } else {
    set.delete(id)
  }
  emit('update:selectedDriverIds', Array.from(set))
}
</script>

<template>
  <UiDialog v-model:open="openModel">
    <UiDialogContent class="sm:max-w-md">
      <UiDialogHeader>
        <UiDialogTitle>Assign drivers</UiDialogTitle>
        <UiDialogDescription>
          Select one or more drivers to associate with this vehicle.
        </UiDialogDescription>
      </UiDialogHeader>

      <div class="mt-2 space-y-3">
        <UiSeparator />

        <div v-if="loading" class="flex items-center justify-center py-6">
          <UiSpinner class="h-4 w-4 text-muted-foreground" />
        </div>
        <template v-else-if="drivers && drivers.length">
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
          <div v-else class="max-h-100 overflow-y-auto space-y-2 text-sm pr-2">
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
          :disabled="assigning"
          @click="openModel = false"
        >
          Cancel
        </UiButton>
        <UiButton
          type="button"
          :loading="assigning"
          :disabled="assigning"
          @click="emit('submit')"
        >
          Save
        </UiButton>
      </UiDialogFooter>
    </UiDialogContent>
  </UiDialog>
</template>
