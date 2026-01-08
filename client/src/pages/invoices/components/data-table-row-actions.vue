<script setup lang="ts">
import type { Row } from '@tanstack/vue-table'
import type { Component } from 'vue'

import { useRouter } from 'vue-router'

import {
  EllipsisIcon,
  EyeIcon,
  FilePenLineIcon,
  Trash2Icon,
} from '@/composables/use-icons'

import type { IInvoice } from '../models/invoice'

import InvoiceDelete from './invoice-delete.vue'

interface IDataTableRowActionsProps {
  row: Row<IInvoice>
}
const props = defineProps<IDataTableRowActionsProps>()
const invoice = computed(() => props.row.original as IInvoice)
const router = useRouter()

const showComponent = shallowRef<Component | null>(null)

type TCommand = 'view' | 'edit' | 'delete'
function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({
        name: '/invoices/view/[id]',
        params: { id: invoice.value.id.toString() },
      })
      break
    case 'edit':
      router.push({
        name: '/invoices/edit/[id]',
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
          <EllipsisIcon class="size-4" />
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
