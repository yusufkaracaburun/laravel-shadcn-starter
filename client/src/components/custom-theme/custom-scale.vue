<script lang="ts" setup>
import { storeToRefs } from 'pinia'

import { SCALES } from '@/constants/themes'
import { useThemeStore } from '@/stores/theme.store'

const themeStore = useThemeStore()
const { setScale } = themeStore
const { scale } = storeToRefs(themeStore)

watchEffect(() => {
  document.documentElement.classList.remove(...SCALES.map(s => `scale-${s}`))
  document.documentElement.classList.add(`scale-${scale.value}`)
})
</script>

<template>
  <div class="space-y-1.5 pt-6">
    <UiLabel for="scale" class="text-xs">
      Scale
    </UiLabel>
    <div class="grid grid-cols-4 gap-2 py-1.5">
      <UiButton
        v-for="s in SCALES" :key="s"
        variant="outline"
        class="justify-center h-8 px-3"
        :class="s === scale ? 'border-foreground border-2' : ''"
        @click="setScale(s)"
      >
        <span class="text-xs uppercase">{{ s }}</span>
      </UiButton>
    </div>
  </div>
</template>

