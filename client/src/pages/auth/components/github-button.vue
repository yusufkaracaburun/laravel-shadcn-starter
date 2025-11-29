<script lang="ts" setup>
import { Icon } from '@iconify/vue'

interface Props {
  testId?: string
}

withDefaults(defineProps<Props>(), {
  testId: 'github_button',
})
</script>

<template>
  <UiButton variant="outline" class="w-full" :data-testid="testId">
    <Icon icon="simple-icons:github" class="size-5 mr-2" />
    GitHub
  </UiButton>
</template>
