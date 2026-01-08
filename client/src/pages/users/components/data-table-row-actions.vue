<script setup lang="ts">
import type { Row } from '@tanstack/vue-table'
import type { Component } from 'vue'

import { Ellipsis, Eye, FilePenLine, Trash2 } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import type { IUser } from '@/pages/users/models/users'

import UserDelete from './user-delete.vue'
import UserResourceDialog from './user-resource-dialog.vue'

interface DataTableRowActionsProps {
  row: Row<IUser>
}
const props = defineProps<DataTableRowActionsProps>()
const user = computed(() => props.row.original)
const router = useRouter()

const showComponent = shallowRef<Component | null>(null)

type TCommand = 'view' | 'edit' | 'delete'
function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({
        name: '/users/[id]',
        params: { id: user.value.id.toString() },
      })
      break
    case 'edit':
      showComponent.value = UserResourceDialog
      break
    case 'delete':
      showComponent.value = UserDelete
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
      <component :is="showComponent" :user="user" @close="isOpen = false" />
    </UiDialogContent>
  </UiDialog>
</template>
