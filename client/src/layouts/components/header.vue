<script setup lang="ts">
import ThemePopover from '@/components/custom-theme/theme-popover.vue'

import HeaderBreadcrumbs from './header-breadcrumbs.vue'
import HeaderNotifications from './header-notifications.vue'
import HeaderSearch from './header-search.vue'
import HeaderUserMenu from './header-user-menu.vue'
</script>

<template>
  <header
    class="sticky top-0 z-50 flex items-center gap-3 sm:gap-4 h-16 px-4 shrink-0 transition-[width,height] ease-linear border-b bg-background"
  >
    <!-- Left Section: Sidebar Toggle + Breadcrumbs -->
    <div class="flex items-center gap-3 sm:gap-4">
      <UiSidebarTrigger
        class="-ml-1"
        data-testid="default-layout_sidebar-toggle"
      />
      <UiSeparator orientation="vertical" class="h-6" />
      <HeaderBreadcrumbs />
    </div>

    <!-- Right Section: Search + Icons -->
    <div class="flex-1" />
    <div class="flex items-center gap-2">
      <!-- Search Bar -->
      <HeaderSearch />

      <!-- Notifications Dropdown -->
      <HeaderNotifications />

      <!-- Theme Popover -->
      <ThemePopover />

      <!-- User Avatar Dropdown -->
      <HeaderUserMenu />
    </div>
  </header>
</template>
