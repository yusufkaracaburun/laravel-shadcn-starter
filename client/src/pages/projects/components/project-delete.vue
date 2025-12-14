<script lang="ts" setup>
import type { Project } from '../data/schema'

import { useProjects } from '@/composables/use-projects'

const props = defineProps<{
  project: Project
}>()

const emits = defineEmits<{
  close: []
}>()

const { deleteProject } = useProjects()
const isDeleting = ref(false)

async function handleRemove() {
  if (!props.project?.id) {
    return
  }

  try {
    isDeleting.value = true
    await deleteProject(props.project.id)
    emits('close')
  }
  catch (error) {
    // Error handling is done in the composable
    console.error('Project deletion error:', error)
  }
  finally {
    isDeleting.value = false
  }
}
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>Delete Project</UiDialogTitle>
      <UiDialogDescription class="mt-2">
        Are you sure you want to delete <strong>{{ project.name }}</strong>? This action cannot be undone.
      </UiDialogDescription>
    </UiDialogHeader>
    <UiDialogFooter>
      <UiDialogClose as-child>
        <UiButton variant="outline">
          Cancel
        </UiButton>
      </UiDialogClose>
      <UiButton
        variant="destructive"
        :disabled="isDeleting"
        @click="handleRemove"
      >
        <UiSpinner v-if="isDeleting" class="mr-2" />
        Delete
      </UiButton>
    </UiDialogFooter>
  </div>
</template>

