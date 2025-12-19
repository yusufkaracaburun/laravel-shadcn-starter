<script setup lang="ts">
import type { Row } from '@tanstack/vue-table'
import type { Component } from 'vue'

import { Ellipsis, Eye, FilePenLine, Trash2 } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import type { TInvoice } from '../data/schema'

import { invoiceSchema } from '../data/schema'
import InvoiceDelete from './invoice-delete.vue'

interface IDataTableRowActionsProps {
  row: Row<TInvoice>
}
const props = defineProps<IDataTableRowActionsProps>()
const invoice = computed(() => {
  const result = invoiceSchema.safeParse(props.row.original)
  if (result.success) {
    return result.data
  }
  // If validation fails, return the original data as-is (type assertion)
  // This handles cases where backend returns slightly different structure
  return props.row.original as TInvoice
})
const router = useRouter()

const showComponent = shallowRef<Component | null>(null)

type TCommand = 'view' | 'edit' | 'delete'
function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({
        name: '/invoices/[id]/view',
        params: { id: invoice.value.id.toString() },
      })
      break
    case 'edit':
      router.push({
        name: '/invoices/edit-[id]',
        params: { id: invoice.value.id.toString() },
      })
      break
    case 'delete':
      showComponent.value = InvoiceDelete
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

    <UiDialogContent>
      <component
        :is="showComponent"
        :invoice="invoice"
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
