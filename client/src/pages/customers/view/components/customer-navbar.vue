<script setup lang="ts">
import { ref } from 'vue'

import type { ICustomer } from '@/pages/customers/models/customers'

import DetailsNavbar from '@/components/global-layout/components/details-page/details-navbar.vue'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import {
  FilePenLineIcon,
  MoreVerticalIcon,
  PlusIcon,
  Trash2Icon,
} from '@/composables/use-icons.composable'
import CustomerDelete from '@/pages/customers/components/customer-delete.vue'
import CustomerEditDialog from '@/pages/customers/components/customer-edit-dialog.vue'

const props = defineProps<{
  customer: ICustomer
}>()

const emits = defineEmits<{
  (e: 'edit-closed'): void
  (e: 'delete-closed'): void
}>()

const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)

const extraMenuItems = [
  {
    label: 'Optie 1',
    icon: MoreVerticalIcon,
    action: 'extra',
  },
  {
    label: 'Bewerken',
    icon: FilePenLineIcon,
    action: 'edit',
  },
  {
    label: 'Verwijderen',
    icon: Trash2Icon,
    action: 'delete',
    variant: 'destructive' as const,
  },
]

const addMenuItems = [
  {
    label: 'Nieuwe factuur',
    icon: PlusIcon,
    action: 'add-invoice',
  },
  {
    label: 'Nieuwe contactpersoon',
    icon: PlusIcon,
    action: 'add-contact',
  },
]

function handleAction(action: string) {
  if (action === 'edit') {
    isEditDialogOpen.value = true
  } else if (action === 'delete') {
    isDeleteDialogOpen.value = true
  }
}

function handleAdd(action: string) {
  // Placeholder
}

function handleEdit() {
  isEditDialogOpen.value = true
}

function handleDelete() {
  isDeleteDialogOpen.value = true
}

function handleEditClose() {
  isEditDialogOpen.value = false
  emits('edit-closed')
}

function handleDeleteClose() {
  isDeleteDialogOpen.value = false
  emits('delete-closed')
}
</script>

<template>
  <DetailsNavbar
    :extra-menu-items="extraMenuItems"
    :add-menu-items="addMenuItems"
    :entity="customer"
    @edit="handleEdit"
    @delete="handleDelete"
    @action="handleAction"
    @add="handleAdd"
  >
    <template #edit-dialog="{ entity }">
      <CustomerEditDialog
        v-if="entity"
        :customer="entity"
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
          <CustomerDelete
            :customer="entity"
            @close="handleDeleteClose"
          />
        </DialogContent>
      </Dialog>
    </template>
  </DetailsNavbar>
</template>
