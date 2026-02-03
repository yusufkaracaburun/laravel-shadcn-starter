<script setup lang="ts">
import type { HTMLAttributes } from 'vue'

import { cn } from '@/lib/utils'

import type { TabSection } from './types'

import DetailsField from './details-field.vue'
import DetailsSection from './details-section.vue'

interface Props {
  sections?: TabSection[]
  spacing?: 'sm' | 'md' | 'lg'
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  sections: () => [],
  spacing: 'md',
  class: undefined,
})

const spacingClasses = {
  sm: 'space-y-2',
  md: 'space-y-2',
  lg: 'space-y-3',
} as const

const containerClass = computed(() =>
  cn(spacingClasses[props.spacing], props.class),
)
</script>

<template>
  <div
    data-slot="details-tabs"
    :class="containerClass"
  >
    <!-- Render sections from props -->
    <template v-for="(section, index) in sections" :key="index">
      <DetailsSection :title="section.title">
        <template v-if="section.fields && section.fields.length > 0">
          <div class="grid gap-2.5 md:grid-cols-2">
            <DetailsField
              v-for="(field, fieldIndex) in section.fields"
              :key="fieldIndex"
              :label="field.label"
              :value="field.value"
              :icon="field.icon"
              :span="field.span"
            />
          </div>
        </template>
        <slot :name="`section-${index}`" :section="section" :index="index" />
      </DetailsSection>
    </template>

    <!-- Default slot for custom content -->
    <slot />
  </div>
</template>
