import { defineStore } from 'pinia'

import type { ContentLayout, Radius, Scale, Theme } from '@/constants/themes'

export const useThemeStore = defineStore(
  'system-config',
  () => {
    const radius = ref(0.5)
    function setRadius(newRadius: Radius) {
      radius.value = newRadius
    }
    const theme = ref<Theme>('zinc')
    function setTheme(newTheme: Theme) {
      theme.value = newTheme
    }

    const contentLayout = ref<ContentLayout>('centered')
    function setContentLayout(newContentLayout: ContentLayout) {
      contentLayout.value = newContentLayout
    }

    const scale = ref<Scale>('sm')
    function setScale(newScale: Scale) {
      scale.value = newScale
    }

    return {
      radius,
      setRadius,

      theme,
      setTheme,

      contentLayout,
      setContentLayout,

      scale,
      setScale,
    }
  },
  {
    persist: true,
  },
)
