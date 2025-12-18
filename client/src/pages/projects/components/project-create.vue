<script lang="ts" setup>
import { FolderPlus } from 'lucide-vue-next'

import ProjectResource from './project-resource.vue'

const isOpen = ref(false)
</script>

<template>
  <UiDrawer v-model:open="isOpen" direction="right">
    <UiDrawerTrigger as-child>
      <UiButton>
        <FolderPlus />
        Create Project
      </UiButton>
    </UiDrawerTrigger>

    <UiDrawerContent class="px-4 pb-4">
      <div class="overflow-y-auto max-h-[calc(100vh-4rem)]">
        <ProjectResource @close="isOpen = false" />
      </div>
    </UiDrawerContent>
  </UiDrawer>
</template>
