<script lang="ts" setup>
import { Plus } from 'lucide-vue-next'

import ProjectResourceDialog from './project-resource-dialog.vue'

const isOpen = ref(false)
</script>

<template>
  <UiDialog v-model:open="isOpen">
    <UiDialogTrigger as-child>
      <UiButton>
        Create
        <Plus />
      </UiButton>
    </UiDialogTrigger>

    <UiDialogContent>
      <ProjectResourceDialog :project="null" @close="isOpen = false" />
    </UiDialogContent>
  </UiDialog>
</template>

