<script setup lang="ts">
import type { Row } from '@tanstack/vue-table'
import type { Component } from 'vue'

import { Ellipsis, Eye, FilePenLine, Trash2 } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import type { Item } from '../data/schema'

import { itemSchema } from '../data/schema'
import ItemDelete from './item-delete.vue'
import ItemResourceDialog from './item-resource-dialog.vue'

interface DataTableRowActionsProps {
  row: Row<Item>
}
const props = defineProps<DataTableRowActionsProps>()
const item = computed(() => itemSchema.parse(props.row.original))
const router = useRouter()

const showComponent = shallowRef<Component | null>(null)

type TCommand = 'view' | 'edit' | 'delete'
function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({
        name: '/items/[id]',
        params: { id: item.value.id.toString() },
      })
      break
    case 'edit':
      showComponent.value = ItemResourceDialog
      isOpen.value = true
      break
    case 'delete':
      showComponent.value = ItemDelete
      isOpen.value = true
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
      <UiDropdownMenuContent align="end">
        <UiDropdownMenuItem @click="handleSelect('view')">
          <Eye class="mr-2 size-4" />
          View
        </UiDropdownMenuItem>
        <UiDropdownMenuItem @click="handleSelect('edit')">
          <FilePenLine class="mr-2 size-4" />
          Edit
        </UiDropdownMenuItem>
        <UiDropdownMenuItem
          class="text-destructive"
          @click="handleSelect('delete')"
        >
          <Trash2 class="mr-2 size-4" />
          Delete
        </UiDropdownMenuItem>
      </UiDropdownMenuContent>
    </UiDropdownMenu>

    <UiDialogContent v-if="showComponent" class="sm:max-w-[425px]">
      <ItemResourceDialog
        v-if="showComponent === ItemResourceDialog"
        :item="item"
        @close="
          () => {
            isOpen = false
            showComponent = null
          }
        "
      />
      <ItemDelete
        v-else-if="showComponent === ItemDelete"
        :item="item"
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
