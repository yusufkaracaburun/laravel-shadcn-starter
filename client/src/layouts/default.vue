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

<!-- <template>
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
</template> -->

<template>
  <UiSidebarProvider
    :default-open="defaultOpen.get('sidebar:state')" :style="{
      '--sidebar-width': 'calc(var(--spacing) * 72)',
      '--header-height': 'calc(var(--spacing) * 12)',
    }"
  >
    <AppSidebar variant="inset" />
    <UiSidebarInset>
      <Header />
      <div class="flex flex-1 flex-col">
        <div class="@container/main flex flex-1 flex-col gap-2">
          <div class="flex flex-col gap-4 py-4 md:gap-6 md:py-6">
            <div class="px-4 lg:px-6">
              <router-view />
            </div>
          </div>
        </div>
      </div>
    </UiSidebarInset>
  </UiSidebarProvider>
</template>
