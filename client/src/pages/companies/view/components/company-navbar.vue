<script setup lang="ts">
import { ref, shallowRef } from 'vue'
import { useRouter } from 'vue-router'

import type { ICompany } from '@/pages/companies/models/companies'

import { Button } from '@/components/ui/button'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import {
  ArrowLeftIcon,
  FilePenLineIcon,
  Trash2Icon,
} from '@/composables/use-icons.composable'
import CompanyDelete from '@/pages/companies/components/company-delete.vue'
import CompanyEditDialog from '@/pages/companies/components/company-edit-dialog.vue'

const props = defineProps<{
  company: ICompany
}>()

const emits = defineEmits<{
  (e: 'edit-closed'): void
  (e: 'delete-closed'): void
}>()

const router = useRouter()

const showComponent = shallowRef<typeof CompanyDelete | null>(null)
const isDialogOpen = ref(false)
const isEditDialogOpen = ref(false)

type TCommand = 'edit' | 'delete' | 'close' | 'back'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      isEditDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = CompanyDelete
      isDialogOpen.value = true
      break
    case 'close':
      isDialogOpen.value = false
      showComponent.value = null
      break
    case 'back':
      router.push('/companies')
      break
  }
}

function handleEditClose() {
  isEditDialogOpen.value = false
  emits('edit-closed')
}

function handleDeleteClose() {
  handleSelect('close')
  emits('delete-closed')
}
</script>

<template>
  <div class="flex items-center gap-2">
    <Button variant="outline" @click="handleSelect('back')">
      <ArrowLeftIcon class="mr-2 size-4" />
      Back
    </Button>
    <Button variant="outline" @click="handleSelect('edit')">
      <FilePenLineIcon class="mr-2 size-4" />
      Edit
    </Button>
    <Button variant="destructive" @click="handleSelect('delete')">
      <Trash2Icon class="mr-2 size-4" />
      Delete
    </Button>
  </div>

  <CompanyEditDialog
    v-if="props.company"
    :company="props.company"
    :open="isEditDialogOpen"
    @update:open="isEditDialogOpen = $event"
    @close="handleEditClose"
  />

  <Dialog v-model:open="isDialogOpen" class="print:hidden">
    <DialogContent v-if="showComponent && props.company" class="sm:max-w-[425px]">
      <CompanyDelete
        v-if="showComponent === CompanyDelete"
        :company="props.company"
        @close="handleDeleteClose"
      />
    </DialogContent>
  </Dialog>
</template>
