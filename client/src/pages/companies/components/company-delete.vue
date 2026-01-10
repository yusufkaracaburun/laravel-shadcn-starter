<script lang="ts" setup>
import type { ICompany } from '@/pages/companies/models/companies'

import { Button } from '@/components/ui/button'
import { useCompanies } from '@/composables/use-companies.composable'
import { useToast } from '@/composables/use-toast.composable'

const props = defineProps<{
  company: ICompany
}>()

const emits = defineEmits<{
  close: []
}>()

const toast = useToast()
const { deleteCompany, isDeleting } = useCompanies()

async function handleDelete() {
  try {
    await deleteCompany(props.company.id)
    emits('close')
  } catch (error: any) {
    // Error handling is done in composable
  }
}
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>Delete Company</UiDialogTitle>
      <UiDialogDescription class="mt-2">
        Are you sure you want to delete <strong>{{ company.name }}</strong>? This action cannot be undone.
      </UiDialogDescription>
    </UiDialogHeader>
    <UiDialogFooter>
      <UiDialogClose as-child>
        <Button variant="outline">
          Cancel
        </Button>
      </UiDialogClose>
      <Button
        variant="destructive"
        :disabled="isDeleting"
        @click="handleDelete"
      >
        <UiSpinner v-if="isDeleting" class="mr-2" />
        Delete
      </Button>
    </UiDialogFooter>
  </div>
</template>
