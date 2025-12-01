<script lang="ts" setup>
import { storeToRefs } from 'pinia'
import { computed } from 'vue'

import { useAuthStore } from '@/stores/auth.store'

import type { User as SidebarUser } from './types'

import { sidebarData } from './data/sidebar-data'
import NavFooter from './nav-footer.vue'
import NavTeam from './nav-team.vue'
import TeamSwitcher from './team-switcher.vue'

const authStore = useAuthStore()
const { user: authUser } = storeToRefs(authStore)

// Map API user to sidebar user format (with avatar fallback)
const sidebarUser = computed<SidebarUser>(() => {
  if (authUser.value) {
    return {
      name: authUser.value.name,
      email: authUser.value.email,
      avatar: '/placeholder.png', // Default avatar since API doesn't provide one
    }
  }
  // Fallback to mock user if not authenticated (shouldn't happen if auth guard works)
  return sidebarData.user
})
</script>

<template>
  <UiSidebar collapsible="icon" class="z-50">
    <UiSidebarHeader>
      <TeamSwitcher :teams="sidebarData.teams" />
    </UiSidebarHeader>

    <UiSidebarContent>
      <NavTeam :nav-main="sidebarData.navMain" />
    </UiSidebarContent>

    <UiSidebarFooter>
      <NavFooter :user="sidebarUser" />
    </UiSidebarFooter>

    <UiSidebarRail />
  </UiSidebar>
</template>
