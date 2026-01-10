<script setup lang="ts">
import type { Row } from '@tanstack/vue-table'
import type { Component } from 'vue'

import {
  EllipsisIcon,
  EyeIcon,
  FilePenLineIcon,
  Trash2Icon,
} from '@/composables/use-icons.composable'
import { useRouter } from 'vue-router'

import type { ICompany } from '@/pages/companies/models/companies'

import CompanyDelete from './company-delete.vue'
import CompanyResourceDialog from './company-resource-dialog.vue'

interface DataTableRowActionsProps {
  row: Row<ICompany>
}
const props = defineProps<DataTableRowActionsProps>()
const company = computed(() => props.row.original)
const router = useRouter()

const showComponent = shallowRef<Component | null>(null)

type TCommand = 'view' | 'edit' | 'delete'
function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({
        name: '/companies/view/[id]',
        params: { id: company.value.id.toString() },
      })
      break
    case 'edit':
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

        <UiDialogTrigger as-child>
          <UiDropdownMenuItem @select.stop="handleSelect('edit')">
            <span>Edit</span>
            <UiDropdownMenuShortcut>
              <FilePenLineIcon class="size-4" />
            </UiDropdownMenuShortcut>
          </UiDropdownMenuItem>
        </UiDialogTrigger>

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
        :company="company"
        @close="isOpen = false"
      />
    </UiDialogContent>
  </UiDialog>
</template>
