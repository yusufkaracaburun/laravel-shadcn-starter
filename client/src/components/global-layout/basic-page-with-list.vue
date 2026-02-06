<script lang="ts" setup>
import type { LayoutHeaderProps } from './types'

import BasicHeader from './basic-header.vue'

const props = withDefaults(
  defineProps<
    LayoutHeaderProps & {
      /**
       * Controls the mobile detail drawer when `#detail` slot is provided.
       * Desktop detail column is always visible when `#detail` exists.
       */
      detailOpen?: boolean
      defaultDetailOpen?: boolean
    }
  >(),
  {
    defaultDetailOpen: false,
  },
)

const emit = defineEmits<{
  'update:detailOpen': [open: boolean]
}>()

const uncontrolledDetailOpen = ref(props.defaultDetailOpen)

const detailOpenModel = computed<boolean>({
  get() {
    return props.detailOpen ?? uncontrolledDetailOpen.value
  },
  set(open) {
    if (props.detailOpen === undefined) {
      uncontrolledDetailOpen.value = open
    }
    emit('update:detailOpen', open)
  },
})

const openDetail = () => (detailOpenModel.value = true)
const closeDetail = () => (detailOpenModel.value = false)
</script>

<template>
  <div class="flex flex-col h-screen">
    <BasicHeader :title="title" :description="description" :sticky="sticky">
      <template #actions>
        <slot name="actions" />
      </template>
    </BasicHeader>

    <div class="flex-1 min-h-0 py-4">
      <div class="flex flex-col lg:flex-row gap-4 h-full min-h-0">
        <!-- Left / list -->
        <section
          class="flex-1 lg:flex-none lg:w-[26rem] min-h-0 overflow-y-auto"
        >
          <slot
            :open-detail="openDetail"
            :close-detail="closeDetail"
            :detail-open="detailOpenModel"
          />
        </section>

        <!-- Right / detail (desktop) -->
        <section
          v-if="$slots.detail"
          class="hidden lg:block flex-1 min-w-0 min-h-0 overflow-y-auto"
        >
          <slot name="detail" :close="closeDetail" :open="detailOpenModel" />
        </section>
      </div>

      <!-- Mobile detail drawer -->
      <UiDrawer
        v-if="$slots.detail"
        v-model:open="detailOpenModel"
        direction="right"
        class="lg:hidden"
      >
        <UiDrawerContent class="px-4 pb-4">
          <div class="overflow-y-auto max-h-[calc(100vh-4rem)]">
            <slot name="detail" :close="closeDetail" :open="detailOpenModel" />
          </div>
        </UiDrawerContent>
      </UiDrawer>
    </div>
  </div>
</template>
