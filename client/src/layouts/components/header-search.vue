<script setup lang="ts">
import { useEventListener } from '@vueuse/core'
import { MenuIcon, Search } from 'lucide-vue-next'
import { computed, ref } from 'vue'

import CommandChangeTheme from '@/components/command-menu-panel/command-change-theme.vue'
import CommandToPage from '@/components/command-menu-panel/command-to-page.vue'
import {
  Empty,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from '@/components/ui/empty'

const commandMenuOpen = ref(false)

useEventListener('keydown', (event: KeyboardEvent) => {
  if (event.key === 'k' && (event.metaKey || event.ctrlKey)) {
    event.preventDefault()
    commandMenuOpen.value = !commandMenuOpen.value
  }
})

const firstKey = computed(() =>
  navigator?.userAgent.includes('Mac OS') ? '⌘' : 'Ctrl',
)
</script>

<template>
  <div>
    <!-- Search Bar -->
    <div
      class="relative flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground border border-border bg-muted/5 rounded-md min-w-[240px] cursor-pointer hover:bg-muted/10 transition-colors"
      data-testid="default-layout_search"
      @click="commandMenuOpen = true"
    >
      <Search class="size-4" />
      <span class="flex-1 text-xs">Search</span>
      <UiKbd class="text-xs">
        {{ firstKey }} K
      </UiKbd>
    </div>

    <!-- Command Menu Dialog -->
    <UiCommandDialog v-model:open="commandMenuOpen">
      <UiCommandInput placeholder="Type a command or search..." />
      <UiCommandList>
        <UiCommandEmpty>
          <Empty>
            <EmptyHeader>
              <EmptyMedia variant="icon">
                <MenuIcon />
              </EmptyMedia>
              <EmptyTitle>No menu found.</EmptyTitle>
              <EmptyDescription>
                Try searching for a command or check the spelling.
              </EmptyDescription>
            </EmptyHeader>
          </Empty>
        </UiCommandEmpty>
        <CommandToPage @click="commandMenuOpen = false" />
        <UiCommandSeparator />
        <CommandChangeTheme @click="commandMenuOpen = false" />
      </UiCommandList>
    </UiCommandDialog>
  </div>
</template>
