<script lang="ts" setup>
import type { LayoutHeaderProps } from './types'

import BasicHeader from './basic-header.vue'

defineProps<LayoutHeaderProps>()
</script>

<template>
  <div class="flex flex-row h-screen gap-4">
    <main class="flex flex-col h-screen w-full">
      <BasicHeader :title="title" :description="description" :sticky="sticky">
        <template #actions>
          <slot name="actions" />
        </template>
      </BasicHeader>
      <main class="py-4 overflow-y-auto">
        <slot />
      </main>
    </main>

    <slot name="row-details" />
  </div>
</template>
