<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { RouterLink, useRouter } from 'vue-router'
import { computed, ref } from 'vue'
import axios from 'axios'

import { useAuthStore } from '@/stores/auth'
import { getCsrfCookie } from '@/services/api/auth.api'
import env from '@/utils/env'

import type { Team } from '@/services/api/auth.api'

const router = useRouter()
const authStore = useAuthStore()
const open = ref(false)

const user = computed(() => authStore.user)
const currentTeam = computed(() => user.value?.currentTeam)
const allTeams = computed(() => user.value?.teams || [])

async function switchToTeam(team: Team) {
  try {
    // Get CSRF cookie first
    await getCsrfCookie()

    // Switch team - this endpoint may need to be created in backend
    const baseURL = env.VITE_SERVER_API_URL
    await axios.put('/current-team', {
      team_id: team.id,
    }, {
      baseURL,
      withCredentials: true,
    })

    // Refresh user data to get updated currentTeam
    await authStore.fetchUser()
    router.push({ name: 'dashboard' })
  }
  catch (error) {
    console.error('Failed to switch team:', error)
  }
}
</script>

<template>
  <UiDropdownMenu v-model:open="open">
    <UiDropdownMenuTrigger as-child>
      <UiSidebarMenuButton
        size="lg"
        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
      >
        <div
          class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground"
        >
          <Icon icon="lucide:rocket" />
        </div>
        <div class="grid flex-1 text-left text-sm leading-tight">
          <span class="truncate font-semibold">{{
            currentTeam?.name ?? 'No Team'
          }}</span>
          <span class="truncate text-xs">Manage Team</span>
        </div>
        <Icon icon="lucide:chevrons-up-down" class="ml-auto size-4" />
      </UiSidebarMenuButton>
    </UiDropdownMenuTrigger>
    <UiDropdownMenuContent
      class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg p-0"
      align="start"
      side="bottom"
      :side-offset="4"
    >
      <UiCommand
        :filter-function="
          (list, term) =>
            list.filter((i: any) =>
              i?.name?.toLowerCase()?.includes(term.toLowerCase()),
            )
        "
      >
        <UiCommandList>
          <UiCommandInput placeholder="Search team..." />
          <UiCommandEmpty>No team found.</UiCommandEmpty>
          <UiCommandGroup heading="Switch Teams">
            <UiCommandItem
              v-for="team in allTeams"
              :key="team.id"
              :value="team"
              @select="
                () => {
                  switchToTeam(team);
                  open = false;
                }
              "
            >
              <UiAvatar class="mr-2 size-5">
                <UiAvatarFallback>
                  {{ team.name?.charAt(0) ?? 'T' }}
                </UiAvatarFallback>
              </UiAvatar>
              {{ team.name }}
              <Icon
                v-if="team.id === currentTeam?.id"
                icon="lucide:check"
                class="ml-auto size-4"
              />
            </UiCommandItem>
          </UiCommandGroup>
        </UiCommandList>
        <UiCommandSeparator
          v-if="allTeams.length > 1"
        />
        <UiCommandGroup heading="Manage Team">
          <UiCommandItem value="team-settings" as-child>
            <RouterLink
              :to="{
                name: 'teams.show',
                params: { team: currentTeam?.id },
              }"
            >
              Team Settings
            </RouterLink>
          </UiCommandItem>
          <UiCommandItem
            value="create-new-team"
            as-child
          >
            <RouterLink :to="{ name: 'teams.create' }">
              Create New Team
            </RouterLink>
          </UiCommandItem>
        </UiCommandGroup>
      </UiCommand>
    </UiDropdownMenuContent>
  </UiDropdownMenu>
</template>

