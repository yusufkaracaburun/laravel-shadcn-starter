<script setup lang="ts">
import { ref, shallowRef } from 'vue'

import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import { Button } from '@/components/ui/button'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  ArrowDownIcon,
  FilePenLineIcon,
  MoreVerticalIcon,
  PlusIcon,
  Trash2Icon,
} from '@/composables/use-icons.composable'
import VehicleDelete from '@/pages/vehicles/components/vehicle-delete.vue'
import VehicleEditDialog from '@/pages/vehicles/components/vehicle-edit-dialog.vue'

const props = defineProps<{
  vehicle: IVehicle
}>()

const emits = defineEmits<{
  (e: 'edit-closed'): void
  (e: 'delete-closed'): void
}>()

const showComponent = shallowRef<typeof VehicleDelete | null>(null)
const isDialogOpen = ref(false)
const isEditDialogOpen = ref(false)

type TCommand = 'edit' | 'delete' | 'close'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      isEditDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = VehicleDelete
      isDialogOpen.value = true
      break
    case 'close':
      isDialogOpen.value = false
      showComponent.value = null
      break
  }
}

function handleEditClose() {
  isEditDialogOpen.value = false
  emits('edit-closed')
}

function handleDeleteClose() {
  handleSelect('close')
  emits('delete-closed')
}

// Placeholder handlers for dropdown actions
function handleExtra() {
  // Placeholder
}

function handleAdd() {
  // Placeholder
}
</script>

<template>
  <div class="flex items-center gap-2">
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="outline">
          Extra
          <ArrowDownIcon class="ml-2 size-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuItem @select="handleExtra">
          <MoreVerticalIcon class="mr-2 size-4" />
          Optie 1
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem @select="handleSelect('edit')">
          <FilePenLineIcon class="mr-2 size-4" />
          Bewerken
        </DropdownMenuItem>
        <DropdownMenuItem variant="destructive" @select="handleSelect('delete')">
          <Trash2Icon class="mr-2 size-4" />
          Verwijderen
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>

    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="default" class="bg-green-600 hover:bg-green-700">
          Toevoegen
          <ArrowDownIcon class="ml-2 size-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuItem @select="handleAdd">
          <PlusIcon class="mr-2 size-4" />
          Nieuwe bestuurder
        </DropdownMenuItem>
        <DropdownMenuItem @select="handleAdd">
          <PlusIcon class="mr-2 size-4" />
          Nieuw document
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>

  <VehicleEditDialog
    v-if="props.vehicle"
    :vehicle="props.vehicle"
    :open="isEditDialogOpen"
    @update:open="isEditDialogOpen = $event"
    @close="handleEditClose"
  />

  <Dialog v-model:open="isDialogOpen" class="print:hidden">
    <DialogContent
      v-if="showComponent && props.vehicle"
      class="sm:max-w-[425px]"
    >
      <VehicleDelete
        v-if="showComponent === VehicleDelete"
        :vehicle="props.vehicle"
        @close="handleDeleteClose"
      />
    </DialogContent>
  </Dialog>
</template>