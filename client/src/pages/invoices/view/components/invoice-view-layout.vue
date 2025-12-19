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
  onRetry: () => { },
})

const errorMessage = computed(() => {
  return (props.errorObject as any)?.message ?? 'An unexpected error occurred.'
})

const errorCode = computed(() => {
  return (props.errorObject as any)?.response?.status || 500
})
</script>

<template>
  <div class="min-h-screen">
    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <Loading />
    </div>

    <div v-else-if="isError" class="flex items-center justify-center min-h-[400px]">
      <div class="text-center">
        <Error :code="errorCode as number" subtitle="Failed to load document" :error="errorMessage" />
        <Button class="mt-4 print:hidden" @click="onRetry">
          Try Again
        </Button>
      </div>
    </div>

    <slot v-else />
  </div>
</template>

<style scoped></style>
