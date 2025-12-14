<script lang="ts" setup>
import type { Project } from '../data/schema'

import ProjectForm from './project-form.vue'

const props = defineProps<{
  project?: Project | null
}>()

defineEmits(['close'])

const title = computed(() => (props.project ? 'Edit Project' : 'Create New Project'))
const description = computed(() =>
  props.project
    ? `Update project information for ${props.project.name}.`
    : 'Add a new project to the system. Fill in the required information below.',
)
</script>

<template>
  <UiDrawerHeader>
    <UiDrawerTitle>
      {{ title }}
    </UiDrawerTitle>
    <UiDrawerDescription>
      {{ description }}
    </UiDrawerDescription>
  </UiDrawerHeader>

  <ProjectForm :project="project" class="mt-4" @close="$emit('close')" />
</template>
