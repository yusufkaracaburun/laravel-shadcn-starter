<script setup lang="ts">
import { computed, ref } from 'vue'

import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import DetailsNavbar from '@/components/global-layout/components/details-page/details-navbar.vue'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import {
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
  editClosed: []
  deleteClosed: []
}>()

const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)

const extraMenuItems = computed(() => [
  {
    label: 'Deactiveren',
    icon: XCircleIcon,
    action: 'deactivate',
  },
  {
    label: 'Archiveren',
    icon: FileTextIcon,
    action: 'archive',
  },
  {
    label: 'Dupliceren',
    icon: PlusIcon,
    action: 'duplicate',
  },
  {
    label: 'Bewerken',
    icon: undefined,
    action: 'edit',
  },
  {
    label: 'Verwijderen',
    icon: Trash2Icon,
    action: 'delete',
    variant: 'destructive' as const,
  },
])

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

function handleAction(action: string) {
  if (action === 'edit') {
    isEditDialogOpen.value = true
  } else if (action === 'delete') {
    isDeleteDialogOpen.value = true
  }
  // Other actions (deactivate, archive, duplicate) can be handled here
}

function handleAdd(action: string) {
  // TODO: Implement actual handlers for each action
  // eslint-disable-next-line no-console
  console.log('Add action:', action)
}

function handleEdit() {
  isEditDialogOpen.value = true
}

function handleDelete() {
  isDeleteDialogOpen.value = true
}

function handleEditClose() {
  isEditDialogOpen.value = false
  emits('editClosed')
}

function handleDeleteClose() {
  isDeleteDialogOpen.value = false
  emits('deleteClosed')
}
</script>

<template>
  <DetailsNavbar
    :extra-menu-items="extraMenuItems"
    :add-menu-items="addMenuItems"
    :show-edit-button="true"
    extra-button-label="Meer"
    :entity="vehicle"
    @edit="handleEdit"
    @delete="handleDelete"
    @action="handleAction"
    @add="handleAdd"
  >
    <template #edit-dialog="{ entity }">
      <VehicleEditDialog
        v-if="entity"
        :vehicle="entity"
        :open="isEditDialogOpen"
        @update:open="isEditDialogOpen = $event"
        @close="handleEditClose"
      />
    </template>

    <template #delete-dialog="{ entity }">
      <Dialog v-model:open="isDeleteDialogOpen" class="print:hidden">
        <DialogContent
          v-if="entity"
          class="sm:max-w-[425px]"
        >
          <VehicleDelete
            :vehicle="entity"
            @close="handleDeleteClose"
          />
        </DialogContent>
      </Dialog>
    </template>
  </DetailsNavbar>
</template>
