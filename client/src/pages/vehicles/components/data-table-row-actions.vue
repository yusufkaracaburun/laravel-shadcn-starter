<script setup lang="ts">
import type { Component } from 'vue'

import { Ellipsis, Eye, FilePenLine, Trash2 } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import type { IDataTableRowActionsProps } from '@/components/data-table/types'
import type { IVehicle } from '@/pages/vehicles/models/vehicles'

import VehicleDelete from './vehicle-delete.vue'
import VehicleEditDialog from './vehicle-edit-dialog.vue'

const props = defineProps<IDataTableRowActionsProps<IVehicle>>()
const vehicle = computed(() => props.row.original)
const router = useRouter()

const showComponent = shallowRef<Component | null>(null)
const isEditDialogOpen = ref(false)

type TCommand = 'view' | 'edit' | 'delete'
function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      // Navigate to view page if it exists
      // router.push({
      //   name: '/vehicles/view/[id]',
      //   params: { id: vehicle.value.id.toString() },
      // })
      break
    case 'edit':
      isEditDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = VehicleDelete
      break
  }
}

const isOpen = ref(false)
</script>

<template>
  <UiDialog v-model:open="isOpen">
    <UiDropdownMenu>
      <UiDropdownMenuTrigger as-child>
        <UiButton
          variant="ghost"
          class="flex h-8 w-8 p-0 data-[state=open]:bg-muted"
        >
          <Ellipsis class="size-4" />
          <span class="sr-only">Open menu</span>
        </UiButton>
      </UiDropdownMenuTrigger>
      <UiDropdownMenuContent align="end" class="w-[160px]">
        <UiDropdownMenuItem @select.stop="handleSelect('view')">
          <span>View</span>
          <UiDropdownMenuShortcut>
            <Eye class="size-4" />
          </UiDropdownMenuShortcut>
        </UiDropdownMenuItem>

        <UiDropdownMenuItem @select.stop="handleSelect('edit')">
          <span>Edit</span>
          <UiDropdownMenuShortcut>
            <FilePenLine class="size-4" />
          </UiDropdownMenuShortcut>
        </UiDropdownMenuItem>

        <UiDialogTrigger as-child>
          <UiDropdownMenuItem @select.stop="handleSelect('delete')">
            <span>Delete</span>
            <UiDropdownMenuShortcut>
              <Trash2 class="size-4" />
            </UiDropdownMenuShortcut>
          </UiDropdownMenuItem>
        </UiDialogTrigger>
      </UiDropdownMenuContent>
    </UiDropdownMenu>

    <UiDialogContent class="sm:max-w-[425px]">
      <component
        :is="showComponent"
        :vehicle="vehicle"
        @close="isOpen = false"
      />
    </UiDialogContent>
  </UiDialog>

  <VehicleEditDialog
    :vehicle="vehicle"
    :open="isEditDialogOpen"
    @update:open="isEditDialogOpen = $event"
    @close="isEditDialogOpen = false"
  />
</template>
