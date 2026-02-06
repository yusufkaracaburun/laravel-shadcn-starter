<script lang="ts" setup>
import { computed } from 'vue'

import Error from '@/components/custom-error.vue'
import Loading from '@/components/loading.vue'
import { Button } from '@/components/ui/button'

import type { LayoutHeaderProps } from './types'

import BasicHeader from './basic-header.vue'

interface Props extends LayoutHeaderProps {
  isLoading?: boolean
  isError?: boolean
  errorObject?: any
  onRetry?: () => void
  errorEntityName?: string
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
  isError: false,
  errorObject: null,
  onRetry: () => {},
  errorEntityName: 'Item',
})

const errorCode = computed(() => {
  return (props.errorObject as any)?.response?.status || 500
})

const isNotFound = computed(() => {
  return errorCode.value === 404
})

const errorSubtitle = computed(() => {
  if (isNotFound.value) {
    return `${props.errorEntityName} Not Found`
  }
  return `Error Loading ${props.errorEntityName}`
})

const errorText = computed(() => {
  if (isNotFound.value) {
    return `The ${props.errorEntityName.toLowerCase()} you are looking for might have been removed or doesn't exist.`
  }
  return `An error occurred while loading the ${props.errorEntityName.toLowerCase()} information. Please try again.`
})
</script>

<template>
  <div class="flex flex-col h-screen">
    <BasicHeader :title="title" :description="description" :sticky="sticky">
      <template #actions>
        <slot name="actions" />
      </template>
    </BasicHeader>

    <!-- Loading State -->
    <div
      v-if="isLoading"
      class="flex items-center justify-center flex-1 min-h-[400px]"
    >
      <Loading />
    </div>

    <!-- Error States -->
    <div
      v-else-if="isError"
      class="flex items-center justify-center flex-1 min-h-[400px]"
    >
      <div class="text-center">
        <Error
          :code="errorCode as number"
          :subtitle="errorSubtitle"
          :error="errorText"
        />
        <Button v-if="!isNotFound" class="mt-4 print:hidden" @click="onRetry">
          Retry
        </Button>
      </div>
    </div>

    <!-- Content -->
    <div v-else class="flex h-full py-4 overflow-hidden">
      <!-- Left Sidebar -->
      <aside class="shrink-0">
        <slot name="sidebar" />
      </aside>

      <!-- Right Main Panel -->
      <main class="flex-1 flex flex-col min-w-0">
        <slot name="tabs" />
      </main>
    </div>
  </div>
</template>
