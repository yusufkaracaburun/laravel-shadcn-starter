<script setup lang="ts">
import type { Component } from 'vue'

import { useRouter } from 'vue-router'

import type { IDataTableRowActionsProps } from '@/components/data-table/types'

import {
  EllipsisIcon,
  EyeIcon,
  FilePenLineIcon,
  Trash2Icon,
} from '@/composables/use-icons.composable'

import type { ICustomer } from '../models/customers'

import CustomerDelete from './customer-delete.vue'
import CustomerEditDialog from './customer-edit-dialog.vue'

const props = defineProps<IDataTableRowActionsProps<ICustomer>>()
const customer = computed(() => props.row.original)
const router = useRouter()

const showComponent = shallowRef<Component | null>(null)
const isEditDialogOpen = ref(false)

type TCommand = 'view' | 'edit' | 'delete'
function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({
        name: '/customers/view/[id]',
        params: { id: customer.value.id.toString() },
      })
      break
    case 'edit':
      isEditDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = CustomerDelete
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
          <EllipsisIcon class="size-4" />
          <span class="sr-only">Open menu</span>
        </UiButton>
      </UiDropdownMenuTrigger>
      <UiDropdownMenuContent align="end" class="w-[160px]">
        <UiDropdownMenuItem @select.stop="handleSelect('view')">
          <span>View</span>
          <UiDropdownMenuShortcut>
            <EyeIcon class="size-4" />
          </UiDropdownMenuShortcut>
        </UiDropdownMenuItem>

        <UiDropdownMenuItem @select.stop="handleSelect('edit')">
          <span>Edit</span>
          <UiDropdownMenuShortcut>
            <FilePenLineIcon class="size-4" />
          </UiDropdownMenuShortcut>
        </UiDropdownMenuItem>

        <UiDialogTrigger as-child>
          <UiDropdownMenuItem @select.stop="handleSelect('delete')">
            <span>Delete</span>
            <UiDropdownMenuShortcut>
              <Trash2Icon class="size-4" />
            </UiDropdownMenuShortcut>
          </UiDropdownMenuItem>
        </UiDialogTrigger>
      </UiDropdownMenuContent>
    </UiDropdownMenu>

    <UiDialogContent class="sm:max-w-[425px]">
      <component
        :is="showComponent"
        :customer="customer"
        @close="isOpen = false"
      />
    </UiDialogContent>
  </UiDialog>

  <CustomerEditDialog
    :customer="customer"
    :open="isEditDialogOpen"
    @update:open="isEditDialogOpen = $event"
    @close="isEditDialogOpen = false"
  />
</template>
