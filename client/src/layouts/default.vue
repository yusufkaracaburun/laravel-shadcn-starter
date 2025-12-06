<script setup lang="ts">
import { useCookies } from '@vueuse/integrations/useCookies'
import { storeToRefs } from 'pinia'

import AppSidebar from '@/components/app-sidebar/index.vue'
import { cn } from '@/lib/utils'
import { useThemeStore } from '@/stores/theme.store'

import Header from './components/header.vue'

const defaultOpen = useCookies(['sidebar:state'])
const themeStore = useThemeStore()
const { contentLayout } = storeToRefs(themeStore)
</script>

<template>
  <UiSidebarProvider :default-open="defaultOpen.get('sidebar:state')">
    <AppSidebar />
    <UiSidebarInset
      class="w-full max-w-full peer-data-[state=collapsed]:w-[calc(100%-var(--sidebar-width-icon)-1rem)] peer-data-[state=expanded]:w-[calc(100%-var(--sidebar-width))]"
    >
      <Header />

      <div :class="cn('p-4 grow', contentLayout === 'centered' ? 'container mx-auto ' : '')">
        <router-view />
      </div>
    </UiSidebarInset>
  </UiSidebarProvider>
</template>
