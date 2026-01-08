<script lang="ts" setup>
import type { Company } from '@/services/companies.service'

import { Button } from '@/components/ui/button'
import { useToast } from '@/composables/use-toast'
import { useDeleteCompanyMutation } from '@/services/companies.service'
import { useErrorStore } from '@/stores/error.store'

const props = defineProps<{
  company: Company
}>()

const emits = defineEmits<{
  close: []
}>()

const toast = useToast()
const errorStore = useErrorStore()
const deleteCompanyMutation = useDeleteCompanyMutation()

async function handleDelete() {
  try {
    await deleteCompanyMutation.mutateAsync(props.company.id)
    toast.showSuccess('Company deleted successfully!')
    emits('close')
  } catch (error: any) {
    // Store error with context
    errorStore.setError(error, { context: 'deleteCompany' })

    // Use error store utilities for messages
    const message = errorStore.getErrorMessage(error)
    toast.showError(message)
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
        :disabled="deleteCompanyMutation.isPending.value"
        @click="handleDelete"
      >
        <UiSpinner v-if="deleteCompanyMutation.isPending.value" class="mr-2" />
        Delete
      </Button>
    </UiDialogFooter>
  </div>
</template>
