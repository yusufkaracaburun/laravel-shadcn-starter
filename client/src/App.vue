<script setup lang="ts">
import { storeToRefs } from 'pinia'

import Loading from '@/components/loading.vue'
import { Toaster } from '@/components/ui/sonner'
import { defaultToasterProps } from '@/composables/use-toast.composable'
import { SCALES, THEMES } from '@/constants/themes'
import { useThemeStore } from '@/stores/theme.store'

const themeStore = useThemeStore()
const { theme: t, radius, scale } = storeToRefs(themeStore)

watchEffect(() => {
  document.documentElement.classList.remove(
    ...THEMES.map(theme => `theme-${theme}`),
  )
  document.documentElement.classList.add(`theme-${t.value}`)
  document.documentElement.style.setProperty('--radius', `${radius.value}rem`)
  document.documentElement.classList.remove(...SCALES.map(s => `scale-${s}`))
  document.documentElement.classList.add(`scale-${scale.value}`)
})
</script>

<template>
  <Toaster v-bind="defaultToasterProps" />

  <Suspense>
    <router-view v-slot="{ Component, route }">
      <component :is="Component" :key="route" />
    </router-view>

    <template #fallback>
      <Loading />
    </template>
  </Suspense>
</template>
