<script setup lang="ts">
import { ref, shallowRef } from 'vue'

import type { ICustomer } from '@/pages/customers/models/customers'

import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import {
  ArrowDownIcon,
  DownloadIcon,
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

const showComponent = shallowRef<typeof CustomerDelete | null>(null)
const isDialogOpen = ref(false)
const isEditDialogOpen = ref(false)

type TCommand = 'edit' | 'delete' | 'close'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      isEditDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = CustomerDelete
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
function handleExport() {
  // Placeholder
}

function handleExtra() {
  // Placeholder
}

function handleAdd() {
  // Placeholder
}
</script>

<template>
  <div class="flex items-center gap-2">
    <Button variant="outline" @click="handleSelect('edit')">
      <FilePenLineIcon class="mr-2 size-4" />
      Bewerken
    </Button>

    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="outline">
          Exporteren
          <ArrowDownIcon class="ml-2 size-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuItem @select="handleExport">
          <DownloadIcon class="mr-2 size-4" />
          Exporteer als PDF
        </DropdownMenuItem>
        <DropdownMenuItem @select="handleExport">
          <DownloadIcon class="mr-2 size-4" />
          Exporteer als CSV
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>

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
        <DropdownMenuItem @select="handleSelect('delete')" variant="destructive">
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
          Nieuwe factuur
        </DropdownMenuItem>
        <DropdownMenuItem @select="handleAdd">
          <PlusIcon class="mr-2 size-4" />
          Nieuwe contactpersoon
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </div>

  <CustomerEditDialog
    v-if="props.customer"
    :customer="props.customer"
    :open="isEditDialogOpen"
    @update:open="isEditDialogOpen = $event"
    @close="handleEditClose"
  />

  <Dialog v-model:open="isDialogOpen" class="print:hidden">
    <DialogContent
      v-if="showComponent && props.customer"
      class="sm:max-w-[425px]"
    >
      <CustomerDelete
        v-if="showComponent === CustomerDelete"
        :customer="props.customer"
        @close="handleDeleteClose"
      />
    </DialogContent>
  </Dialog>
</template>
