<script setup lang="ts">
import { Settings } from 'lucide-vue-next'

import HeaderBreadcrumbs from './header-breadcrumbs.vue'
import HeaderNotifications from './header-notifications.vue'
import HeaderSearch from './header-search.vue'
import HeaderUserMenu from './header-user-menu.vue'
import ToggleTheme from '@/components/toggle-theme.vue'
</script>

<template>
  <header
    class="flex items-center gap-3 sm:gap-4 h-16 px-4 shrink-0 transition-[width,height] ease-linear border-b"
  >
    <!-- Left Section: Sidebar Toggle + Breadcrumbs -->
    <div class="flex items-center gap-3 sm:gap-4">
      <UiSidebarTrigger class="-ml-1" data-testid="default-layout_sidebar-toggle" />
      <UiSeparator orientation="vertical" class="h-6" />
      <HeaderBreadcrumbs />
    </div>

    <!-- Right Section: Search + Icons -->
    <div class="flex-1" />
    <div class="flex items-center gap-2">
      <!-- Search Bar -->
      <HeaderSearch />

      <!-- Theme Toggle -->
      <ToggleTheme />

      <!-- Settings Icon -->
      <UiButton
        variant="ghost"
        size="icon"
        data-testid="default-layout_settings-button"
        @click="$router.push('/settings')"
      >
        <Settings class="size-4" />
      </UiButton>

      <!-- Notifications Dropdown -->
      <HeaderNotifications />

      <!-- User Avatar Dropdown -->
      <HeaderUserMenu />
    </div>
  </header>
</template>

