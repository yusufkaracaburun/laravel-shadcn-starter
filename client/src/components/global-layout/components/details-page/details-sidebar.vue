<script setup lang="ts">
import type { HTMLAttributes } from 'vue'

import InfoItem from '@/components/global-layout/components/info-item.vue'
import InfoSection
  from '@/components/global-layout/components/info-section.vue'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import {
  CalendarIcon,
  UserIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { cn } from '@/lib/utils'

import type {
  RelatedItem,
  SidebarField,
  SidebarStatus,
  SidebarTimestamps,
} from './types'

interface Props {
  title: string
  subtitle?: string
  entityId?: string | number
  status?: SidebarStatus
  fields?: SidebarField[]
  relatedItems?: RelatedItem[]
  relatedItemsLabel?: string
  relatedItemsEmptyText?: string
  relatedItemsMaxDisplay?: number
  showViewAll?: boolean
  viewAllText?: string
  timestamps?: SidebarTimestamps
  onNavigateToTab?: (tab: string) => void
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  subtitle: undefined,
  entityId: undefined,
  status: undefined,
  fields: () => [],
  relatedItems: () => [],
  relatedItemsLabel: 'Related Items',
  relatedItemsEmptyText: 'No items',
  relatedItemsMaxDisplay: 3,
  showViewAll: false,
  viewAllText: 'View all',
  timestamps: undefined,
  onNavigateToTab: undefined,
  class: undefined,
})

const displayedRelatedItems = computed(() => {
  if (!props.relatedItems || props.relatedItems.length === 0) {
    return []
  }
  return props.relatedItems.slice(0, props.relatedItemsMaxDisplay)
})

const remainingRelatedItemsCount = computed(() => {
  if (!props.relatedItems) {
    return 0
  }
  return Math.max(0, props.relatedItems.length - props.relatedItemsMaxDisplay)
})
</script>

<template>
  <aside
    data-slot="details-sidebar"
    :class="cn('w-80 bg-background p-3 space-y-2.5 h-full overflow-y-auto', props.class)"
  >
    <!-- Header -->
    <div>
      <h2 class="text-sm font-semibold mb-1.5">
        {{ title }}
      </h2>
      <slot name="header-content">
        <div class="text-lg font-bold">
          <slot name="header-title" />
        </div>
        <div
          v-if="subtitle"
          class="text-xs text-muted-foreground mt-0.5"
        >
          {{ subtitle }}
        </div>
      </slot>
      <slot name="header-extra" />
    </div>

    <!-- Status -->
    <InfoSection
      v-if="status || $slots.status"
      title="Status"
      title-spacing="lg"
      with-border
    >
      <slot name="status">
        <Badge
          v-if="status"
          :variant="status.variant || 'default'"
          :class="status.color || ''"
        >
          {{ status.label }}
        </Badge>
      </slot>
      <slot name="status-extra" />
    </InfoSection>

    <!-- Fields -->
    <div v-if="fields.length > 0 || $slots.fields">
      <InfoSection
        v-if="fields.length > 0"
        title="Information"
        title-spacing="lg"
        with-border
      >
        <template v-for="(field, index) in fields" :key="index">
          <InfoItem
            v-if="field.value !== null && field.value !== undefined"
            :label="field.label"
            :value="field.value"
            :icon="field.icon"
          />
        </template>
      </InfoSection>
      <slot name="fields" />
      <slot name="fields-extra" />
    </div>

    <!-- Custom Section -->
    <slot name="custom-section" />

    <!-- Related Items -->
    <InfoSection
      v-if="relatedItems.length > 0 || $slots['related-items']"
      :title="relatedItemsLabel"
      with-border
    >
      <div v-if="relatedItems.length === 0 && !$slots['related-items']" class="text-sm text-muted-foreground py-2">
        {{ relatedItemsEmptyText }}
      </div>
      <div v-else class="space-y-1">
        <div
          v-for="item in displayedRelatedItems"
          :key="item.id"
          class="group flex items-center justify-between gap-2 py-1.5 hover:bg-muted/50 rounded transition-colors"
          @click="item.onClick?.()"
        >
          <div class="flex items-center gap-2 flex-1 min-w-0">
            <Avatar class="size-6 shrink-0">
              <AvatarFallback class="bg-muted text-xs">
                <component :is="item.icon" v-if="item.icon" class="size-3" />
                <UserIcon v-else class="size-3" />
              </AvatarFallback>
            </Avatar>
            <div class="flex items-center gap-2">
              <div class="text-sm truncate">
                {{ item.name }}
              </div>
              <component :is="item.badge" v-if="item.badge" />
            </div>
          </div>
        </div>
        <slot name="related-items" />
        <button
          v-if="showViewAll && remainingRelatedItemsCount > 0 && onNavigateToTab"
          type="button"
          class="w-full text-left text-xs font-medium text-primary hover:text-primary/80 transition-colors py-2 px-2 rounded-md hover:bg-primary/5 flex items-center gap-1.5 group"
          @click="() => onNavigateToTab?.('')"
        >
          <span>+{{ remainingRelatedItemsCount }} {{ viewAllText }}</span>
          <UsersIcon class="size-3 opacity-60 group-hover:opacity-100 transition-opacity" />
        </button>
      </div>
      <slot name="related-items-extra" />
    </InfoSection>

    <!-- Timestamps -->
    <InfoSection
      v-if="timestamps || $slots.timestamps"
      title="Time"
      with-border
    >
      <slot name="timestamps">
        <InfoItem
          v-if="timestamps?.created"
          label="Created"
          :value="timestamps.created"
          :icon="CalendarIcon"
        />
        <InfoItem
          v-if="timestamps?.updated"
          label="Updated"
          :value="timestamps.updated"
          :icon="CalendarIcon"
        />
      </slot>
    </InfoSection>

    <!-- Default slot for any additional content -->
    <slot />
  </aside>
</template>
