<script lang="ts" setup>
import type { Project } from '../data/schema'

import ProjectForm from './project-form.vue'

const props = defineProps<{
  project: Project | null
}>()
defineEmits(['close'])

const project = computed(() => props.project)
const title = computed(() => (project.value?.id ? `Edit Project` : 'New Project'))
const description = computed(() =>
  project.value?.id ? `Edit project ${project.value.id}` : 'Create new project',
)
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>
        {{ title }}
      </UiDialogTitle>
      <UiDialogDescription>
        {{ description }}
      </UiDialogDescription>
    </UiDialogHeader>
    <ProjectForm class="mt-2" :project="project" @close="$emit('close')" />
  </div>
</template>
