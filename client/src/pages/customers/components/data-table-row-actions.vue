<script setup lang="ts">
import type { Row } from '@tanstack/vue-table'
import type { Component } from 'vue'

import { Ellipsis, Eye, FilePenLine, Trash2 } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import type { Customer } from '../data/schema'

import { customerSchema } from '../data/schema'
import CustomerDelete from './customer-delete.vue'
import CustomerResourceDialog from './customer-resource-dialog.vue'

interface DataTableRowActionsProps {
  row: Row<Customer>
}
const props = defineProps<DataTableRowActionsProps>()
const customer = computed(() => {
  const result = customerSchema.safeParse(props.row.original)
  if (result.success) {
    return result.data
  }
  // If validation fails, return the original data as-is (type assertion)
  // This handles cases where backend returns slightly different structure
  return props.row.original as Customer
})
const router = useRouter()

const showComponent = shallowRef<Component | null>(null)

type TCommand = 'view' | 'edit' | 'delete'
function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({
        name: '/customers/[id]',
        params: { id: customer.value.id.toString() },
      })
      break
    case 'edit':
      showComponent.value = CustomerResourceDialog
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
        <UiButton variant="ghost" class="size-8 p-0">
          <span class="sr-only">Open menu</span>
          <Ellipsis class="size-4" />
        </UiButton>
      </UiDropdownMenuTrigger>
      <UiDropdownMenuContent align="end" class="w-[160px]">
        <UiDropdownMenuItem @select.stop="handleSelect('view')">
          <span>View</span>
          <UiDropdownMenuShortcut>
            <Eye class="size-4" />
          </UiDropdownMenuShortcut>
        </UiDropdownMenuItem>

        <UiDialogTrigger as-child>
          <UiDropdownMenuItem @select.stop="handleSelect('edit')">
            <span>Edit</span>
            <UiDropdownMenuShortcut>
              <FilePenLine class="size-4" />
            </UiDropdownMenuShortcut>
          </UiDropdownMenuItem>
        </UiDialogTrigger>

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

    <UiDialogContent>
      <component
        :is="showComponent"
        :customer="customer"
        @close="
          () => {
            isOpen = false
            showComponent = null
          }
        "
      />
    </UiDialogContent>
  </UiDialog>
</template>
