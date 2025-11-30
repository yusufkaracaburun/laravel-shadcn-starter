<script lang="ts" setup>
import { Icon } from '@iconify/vue'

interface Props {
  testId?: string
}

withDefaults(defineProps<Props>(), {
  testId: 'google_button',
})
</script>

<template>
  <UiButton variant="outline" class="w-full" :data-testid="testId">
    <Icon icon="simple-icons:google" class="size-5 mr-2" />
    Google
  </UiButton>
</template>
