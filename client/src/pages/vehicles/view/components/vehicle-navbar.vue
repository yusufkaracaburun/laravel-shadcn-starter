<script setup lang="ts">
import { computed, ref, shallowRef } from 'vue'

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
  FileTextIcon,
  PlusIcon,
  Trash2Icon,
  UsersIcon,
  XCircleIcon,
} from '@/composables/use-icons.composable'
import VehicleDelete from '@/pages/vehicles/components/vehicle-delete.vue'
import VehicleEditDialog from '@/pages/vehicles/components/vehicle-edit-dialog.vue'

const props = withDefaults(
  defineProps<{
    vehicle: IVehicle
    activeTab?: 'overview' | 'details' | 'drivers' | 'documents'
  }>(),
  {
    activeTab: 'overview',
  },
)

const emits = defineEmits<{
  (e: 'edit-closed'): void
  (e: 'delete-closed'): void
}>()

const showComponent = shallowRef<typeof VehicleDelete | null>(null)
const isDialogOpen = ref(false)
const isEditDialogOpen = ref(false)

type TCommand = 'edit' | 'delete' | 'deactivate' | 'archive' | 'duplicate' | 'close'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      isEditDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = VehicleDelete
      isDialogOpen.value = true
      break
    case 'deactivate':
      // TODO: Implement vehicle deactivation
      // - Call updateVehicle mutation with status 'inactive'
      // - Show loading state
      // - Handle errors
      // - Refresh vehicle data after success
      break
    case 'archive':
      // TODO: Implement vehicle archiving
      // - Call archiveVehicle mutation/endpoint
      // - Show loading state
      // - Handle errors
      // - Redirect to vehicles list after success
      break
    case 'duplicate':
      // TODO: Implement vehicle duplication
      // - Open duplicate dialog/form
      // - Pre-fill form with vehicle data
      // - Allow user to modify before creating
      // - Call createVehicle mutation
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

// Context-aware add menu items based on active tab
const addMenuItems = computed(() => {
  if (props.activeTab === 'drivers') {
    return [
      {
        label: 'Bestuurder koppelen',
        icon: UsersIcon,
        action: 'assign-driver',
      },
    ]
  }
  if (props.activeTab === 'documents') {
    return [
      {
        label: 'Document uploaden',
        icon: FileTextIcon,
        action: 'upload-document',
      },
    ]
  }
  if (props.activeTab === 'details') {
    return [
      {
        label: 'Bestuurder koppelen',
        icon: UsersIcon,
        action: 'assign-driver',
      },
      {
        label: 'Document uploaden',
        icon: FileTextIcon,
        action: 'upload-document',
      },
    ]
  }
  // Default for 'overview' or undefined
  return [
    {
      label: 'Nieuwe bestuurder',
      icon: PlusIcon,
      action: 'add-driver',
    },
    {
      label: 'Nieuw document',
      icon: PlusIcon,
      action: 'add-document',
    },
  ]
})

// Handlers for add menu actions
function handleAddAction(action: string) {
  // TODO: Implement actual handlers for each action
  // - assign-driver: Open driver assignment dialog
  // - upload-document: Open document upload dialog
  // - add-driver: Open add driver dialog
  // - add-document: Open add document dialog
  // eslint-disable-next-line no-console
  console.log('Add action:', action)
}
</script>

<template>
  <div class="flex items-center gap-2">
    <!-- Optional "Bewerken" button for quick access -->
    <Button variant="outline" @click="handleSelect('edit')">
      <FilePenLineIcon class="mr-2 size-4" />
      Bewerken
    </Button>

    <!-- "Meer" dropdown with actions -->
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="outline">
          Meer
          <ArrowDownIcon class="ml-2 size-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuItem @select="handleSelect('deactivate')">
          <XCircleIcon class="mr-2 size-4" />
          Deactiveren
        </DropdownMenuItem>
        <DropdownMenuItem @select="handleSelect('archive')">
          <FileTextIcon class="mr-2 size-4" />
          Archiveren
        </DropdownMenuItem>
        <DropdownMenuItem @select="handleSelect('duplicate')">
          <PlusIcon class="mr-2 size-4" />
          Dupliceren
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem
          variant="destructive"
          @select="handleSelect('delete')"
        >
          <Trash2Icon class="mr-2 size-4" />
          Verwijderen
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>

    <!-- "Toevoegen" dropdown - context-aware -->
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="default" class="bg-green-600 hover:bg-green-700">
          Toevoegen
          <ArrowDownIcon class="ml-2 size-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuItem
          v-for="item in addMenuItems"
          :key="item.action"
          @select="handleAddAction(item.action)"
        >
          <component :is="item.icon" class="mr-2 size-4" />
          {{ item.label }}
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
