<script setup lang="ts">
import { ref } from 'vue'

import type { HTMLAttributes } from 'vue'

import { Button } from '@/components/ui/button'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { cn } from '@/lib/utils'
import {
  ArrowDownIcon,
  FilePenLineIcon,
  PlusIcon,
} from '@/composables/use-icons.composable'

import type { MenuItem } from './types'

interface Props {
  extraMenuItems?: MenuItem[]
  addMenuItems?: MenuItem[]
  showEditButton?: boolean
  editButtonLabel?: string
  extraButtonLabel?: string
  addButtonLabel?: string
  entity?: unknown
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  extraMenuItems: () => [],
  addMenuItems: () => [],
  showEditButton: false,
  editButtonLabel: 'Bewerken',
  extraButtonLabel: 'Extra',
  addButtonLabel: 'Toevoegen',
  entity: undefined,
  class: undefined,
})

const emits = defineEmits<{
  edit: []
  delete: []
  action: [action: string]
  add: [action: string]
}>()

const isEditDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)

function handleEdit() {
  isEditDialogOpen.value = true
  emits('edit')
}

function handleDelete() {
  isDeleteDialogOpen.value = true
  emits('delete')
}

function handleAction(action: string) {
  if (action === 'edit') {
    handleEdit()
  } else if (action === 'delete') {
    handleDelete()
  } else {
    emits('action', action)
  }
}

function handleAdd(action: string) {
  emits('add', action)
}

function handleEditClose() {
  isEditDialogOpen.value = false
}

function handleDeleteClose() {
  isDeleteDialogOpen.value = false
}
</script>

<template>
  <nav
    data-slot="details-navbar"
    :class="cn('flex items-center gap-2', props.class)"
  >
    <!-- Optional Edit Button -->
    <Button
      v-if="showEditButton"
      variant="outline"
      @click="handleEdit"
    >
      <FilePenLineIcon class="mr-2 size-4" />
      {{ editButtonLabel }}
    </Button>

    <!-- Extra Dropdown -->
    <DropdownMenu v-if="extraMenuItems.length > 0 || $slots.extra-menu">
      <DropdownMenuTrigger as-child>
        <Button variant="outline">
          {{ extraButtonLabel }}
          <ArrowDownIcon class="ml-2 size-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <slot name="extra-menu">
          <template v-for="(item, index) in extraMenuItems" :key="index">
            <DropdownMenuItem
              v-if="item.action !== 'edit' && item.action !== 'delete'"
              :variant="item.variant"
              @select="handleAction(item.action)"
            >
              <component v-if="item.icon" :is="item.icon" class="mr-2 size-4" />
              {{ item.label }}
            </DropdownMenuItem>
          </template>
          <DropdownMenuSeparator v-if="extraMenuItems.some(i => i.action === 'edit' || i.action === 'delete')" />
          <DropdownMenuItem
            v-for="item in extraMenuItems.filter(i => i.action === 'edit')"
            :key="item.action"
            @select="handleEdit"
          >
            <FilePenLineIcon class="mr-2 size-4" />
            {{ item.label }}
          </DropdownMenuItem>
          <DropdownMenuItem
            v-for="item in extraMenuItems.filter(i => i.action === 'delete')"
            :key="item.action"
            variant="destructive"
            @select="handleDelete"
          >
            <component v-if="item.icon" :is="item.icon" class="mr-2 size-4" />
            {{ item.label }}
          </DropdownMenuItem>
        </slot>
      </DropdownMenuContent>
    </DropdownMenu>

    <!-- Add Dropdown -->
    <DropdownMenu v-if="addMenuItems.length > 0 || $slots.add-menu">
      <DropdownMenuTrigger as-child>
        <Button variant="default" class="bg-green-600 hover:bg-green-700">
          {{ addButtonLabel }}
          <ArrowDownIcon class="ml-2 size-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <slot name="add-menu">
          <DropdownMenuItem
            v-for="item in addMenuItems"
            :key="item.action"
            @select="handleAdd(item.action)"
          >
            <component v-if="item.icon" :is="item.icon" class="mr-2 size-4" />
            {{ item.label }}
          </DropdownMenuItem>
        </slot>
      </DropdownMenuContent>
    </DropdownMenu>

    <!-- Additional Actions Slot -->
    <slot name="extra-actions" />
  </nav>

  <!-- Edit Dialog Slot -->
  <slot
    name="edit-dialog"
    :open="isEditDialogOpen"
    :entity="entity"
    :on-close="handleEditClose"
  />

  <!-- Delete Dialog Slot -->
  <slot
    name="delete-dialog"
    :open="isDeleteDialogOpen"
    :entity="entity"
    :on-close="handleDeleteClose"
  />
</template>
