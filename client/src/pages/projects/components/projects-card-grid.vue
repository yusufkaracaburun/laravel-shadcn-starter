<script setup lang="ts">
import type { Project } from '@/services/projects.service'

import Loading from '@/components/loading.vue'

import ProjectCard from './project-card.vue'

interface Props {
  projects: Project[]
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
})
</script>

<template>
  <div v-if="loading" class="flex items-center justify-center min-h-[400px]">
    <Loading />
  </div>
  <div
    v-else-if="projects.length === 0"
    class="flex items-center justify-center min-h-[400px]"
  >
    <div class="text-center">
      <p class="text-muted-foreground">No projects found.</p>
    </div>
  </div>
  <div
    v-else
    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
  >
    <ProjectCard
      v-for="project in projects"
      :key="project.id"
      :project="project"
    />
  </div>
</template>
