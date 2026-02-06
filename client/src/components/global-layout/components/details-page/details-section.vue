<script setup lang="ts">
import type { HTMLAttributes } from 'vue'

import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { cn } from '@/lib/utils'

interface Props {
  title: string
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <Card
    data-slot="details-section"
    :class="cn('border', props.class)"
  >
    <CardHeader class="pb-1.5 pt-2.5 px-4">
      <CardTitle class="text-sm font-semibold">
        {{ title }}
      </CardTitle>
      <slot name="header-extra" />
    </CardHeader>
    <CardContent class="px-4 pb-2.5">
      <slot />
    </CardContent>
  </Card>
</template>
