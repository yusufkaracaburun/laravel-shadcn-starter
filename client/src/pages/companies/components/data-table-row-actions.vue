<script setup lang="ts">
import type { Component } from 'vue'

import {
  EllipsisIcon,
  EyeIcon,
  FilePenLineIcon,
  Trash2Icon,
} from '@/composables/use-icons.composable'
import { useRouter } from 'vue-router'

import type { IDataTableRowActionsProps } from '@/components/data-table/types'
import type { ICompany } from '@/pages/companies/models/companies'

import CompanyDelete from './company-delete.vue'
import CompanyEditDialog from './company-edit-dialog.vue'

const props = defineProps<IDataTableRowActionsProps<ICompany>>()
const company = computed(() => props.row.original)
const router = useRouter()

const showComponent = shallowRef<Component | null>(null)
const isEditDialogOpen = ref(false)

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
      isEditDialogOpen.value = true
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
      <component :is="showComponent" :company="company" @close="isOpen = false" />
    </UiDialogContent>
  </UiDialog>

  <CompanyEditDialog
    :company="company"
    :open="isEditDialogOpen"
    @update:open="isEditDialogOpen = $event"
    @close="isEditDialogOpen = false"
  />
</template>
