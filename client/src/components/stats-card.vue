<script setup lang="ts">
import { Icon } from '@iconify/vue'
</script>

interface Props {
  value: string | number
  description?: string
  link?: string
  icon?: string
}

withDefaults(defineProps<Props>(), {
  description: '',
  link: '',
  icon: 'lucide:info',
})
</script>

<template>
  <UiCard :class="link ? 'hover:shadow-sm cursor-pointer' : ''">
    <a v-if="link" :href="link" target="_blank" rel="noopener" class="block">
      <UiCardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <UiCardTitle class="text-sm font-medium">
          <Icon :icon="icon" class="size-8" />
        </UiCardTitle>
      </UiCardHeader>
      <UiCardContent>
        <div class="text-xl font-bold text-primary">
          {{ value }}
        </div>
        <p class="text-muted-foreground text-sm">
          {{ description }}
        </p>
      </UiCardContent>
    </a>
    <template v-else>
      <UiCardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <UiCardTitle class="text-sm font-medium">
          <Icon :icon="icon" class="size-8" />
        </UiCardTitle>
      </UiCardHeader>
      <UiCardContent>
        <div class="text-xl font-bold text-primary">
          {{ value }}
        </div>
        <p class="text-muted-foreground text-sm">
          {{ description }}
        </p>
      </UiCardContent>
    </template>
  </UiCard>
</template>

