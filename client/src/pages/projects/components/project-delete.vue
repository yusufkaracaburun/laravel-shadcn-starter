<script lang="ts" setup>
import { useProjects } from '@/composables/use-projects'

import type { Project } from '../data/schema'

const props = defineProps<{
  project: Project
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
    <UiDialogTitle>
      <UiDialogTitle> Delete this project: {{ project.id }} ? </UiDialogTitle>
      <UiDialogDescription class="mt-2 font-medium">
        You are about to delete a project with the ID {{ project.id }}.This action cannot be undone.
      </UiDialogDescription>
    </UiDialogTitle>
    <UiDialogFooter>
      <UiDialogClose as-child>
        <UiButton variant="outline"> Cancel </UiButton>
      </UiDialogClose>

      <UiDialogClose as-child>
        <UiButton variant="destructive" :disabled="isDeleting" @click="handleRemove">
          {{ isDeleting ? 'Deleting...' : 'Delete' }}
        </UiButton>
      </UiDialogClose>
    </UiDialogFooter>
  </div>
</template>

