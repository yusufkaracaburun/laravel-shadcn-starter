<script setup lang="ts">
import { computed } from 'vue'

import Error from '@/components/custom-error.vue'
import Loading from '@/components/loading.vue'
import { Button } from '@/components/ui/button'

interface Props {
  isLoading?: boolean
  isError?: boolean
  errorObject?: any
  onRetry?: () => void
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
  isError: false,
  errorObject: null,
  onRetry: () => {},
})

const errorMessage = computed(() => {
  return (props.errorObject as any)?.message ?? 'An unexpected error occurred.'
})

const errorCode = computed(() => {
  return (props.errorObject as any)?.response?.status || 500
})

const isNotFound = computed(() => {
  return errorCode.value === 404
})

const errorSubtitle = computed(() => {
  if (isNotFound.value) {
    return 'Product Not Found'
  }
  return 'Error Loading Product'
})

const errorText = computed(() => {
  if (isNotFound.value) {
    return 'The product you are looking for might have been removed or doesn\'t exist.'
  }
  return 'An error occurred while loading the product information. Please try again.'
})
</script>

<template>
  <div class="min-h-screen">
    <!-- Loading State -->
    <div
      v-if="isLoading"
      class="flex items-center justify-center min-h-[400px]"
    >
      <Loading />
    </div>

    <!-- Error States -->
    <div
      v-else-if="isError"
      class="flex items-center justify-center min-h-[400px]"
    >
      <div class="text-center">
        <Error
          :code="errorCode as number"
          :subtitle="errorSubtitle"
          :error="errorText"
        />
        <Button
          v-if="!isNotFound"
          class="mt-4 print:hidden"
          @click="onRetry"
        >
          Retry
        </Button>
      </div>
    </div>

    <!-- Content Slot -->
    <slot v-else />
  </div>
</template>
