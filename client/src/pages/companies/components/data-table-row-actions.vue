<script setup lang="ts">
import type { Row } from '@tanstack/vue-table'
import type { Component } from 'vue'

import { Ellipsis, FilePenLine, Trash2 } from 'lucide-vue-next'

import type { Company } from '@/services/companies.service'

import CompanyDelete from './company-delete.vue'
import CompanyResourceDialog from './company-resource-dialog.vue'

const props = defineProps<DataTableRowActionsProps>()

interface DataTableRowActionsProps {
  row: Row<Company>
}
const company = computed(() => props.row.original)

const showComponent = shallowRef<Component | null>(null)

type TCommand = 'edit' | 'create' | 'delete'
function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      showComponent.value = CompanyResourceDialog
      break
    case 'create':
      showComponent.value = CompanyResourceDialog
      break
    case 'delete':
      showComponent.value = CompanyDelete
      break
  }
}

const isOpen = ref(false)
</script>

<template>
  <UiDialog v-model:open="isOpen">
    <UiDropdownMenu>
      <UiDropdownMenuTrigger as-child>
        <UiButton variant="ghost" class="flex h-8 w-8 p-0 data-[state=open]:bg-muted">
          <Ellipsis class="size-4" />
          <span class="sr-only">Open menu</span>
        </UiButton>
      </UiDropdownMenuTrigger>
      <UiDropdownMenuContent align="end" class="w-[160px]">
        <UiDialogTrigger as-child>
          <UiDropdownMenuItem @select.stop="handleSelect('edit')">
            <span>Edit</span>
            <UiDropdownMenuShortcut> <FilePenLine class="size-4" /> </UiDropdownMenuShortcut>
          </UiDropdownMenuItem>
        </UiDialogTrigger>

        <UiDropdownMenuItem disabled> Make a copy </UiDropdownMenuItem>
        <UiDropdownMenuItem disabled> Favorite </UiDropdownMenuItem>

        <UiDropdownMenuSeparator />

        <UiDialogTrigger as-child>
          <UiDropdownMenuItem @select.stop="handleSelect('delete')">
            <span>Delete</span>
            <UiDropdownMenuShortcut> <Trash2 class="size-4" /> </UiDropdownMenuShortcut>
          </UiDropdownMenuItem>
        </UiDialogTrigger>
      </UiDropdownMenuContent>
    </UiDropdownMenu>

    <UiDialogContent>
      <component :is="showComponent" :company="company" @close="isOpen = false" />
    </UiDialogContent>
  </UiDialog>
</template>

