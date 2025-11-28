<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { useChangeCase } from '@vueuse/integrations/useChangeCase'

import env from '@/utils/env'

interface Provider {
  slug: string
  icon: string
  name?: string
}

const props = withDefaults(defineProps<{
  provider: Provider
  disabled?: boolean
}>(), {
  disabled: false,
})

// OAuth redirect URL - uses web route, not API route
const oauthUrl = computed(() => {
  const baseURL = env.VITE_SERVER_API_URL
  return `${baseURL}/auth/redirect/${props.provider.slug}`
})

const providerName = computed(() => {
  return useChangeCase(props.provider.slug, 'sentenceCase')
})
</script>

<template>
  <UiButton
    :disabled="disabled"
    class="bg-background text-foreground hover:bg-secondary disabled:opacity-50 dark:hover:bg-primary/80 dark:bg-primary dark:text-primary-foreground"
    as="a"
    :href="oauthUrl"
  >
    <Icon :icon="provider.icon" class="mr-2 h-4 w-4" />
    Sign In With {{ providerName }}
  </UiButton>
</template>

