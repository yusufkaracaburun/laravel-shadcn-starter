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
const customer = computed(() => customerSchema.parse(props.row.original))
const router = useRouter()

const showComponent = shallowRef<Component | null>(null)

type TCommand = 'view' | 'edit' | 'delete'
function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({ name: '/customers/[id]', params: { id: customer.value.id.toString() } })
      break
    case 'edit':
      showComponent.value = CustomerResourceDialog
      isOpen.value = true
      break
    case 'delete':
      showComponent.value = CustomerDelete
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
      <CustomerResourceDialog
        v-if="showComponent === CustomerResourceDialog"
        :customer="customer"
        @close="() => { isOpen = false; showComponent = null }"
      />
      <CustomerDelete
        v-else-if="showComponent === CustomerDelete"
        :customer="customer"
        @close="() => { isOpen = false; showComponent = null }"
      />
    </UiDialogContent>
  </UiDialog>
</template>

