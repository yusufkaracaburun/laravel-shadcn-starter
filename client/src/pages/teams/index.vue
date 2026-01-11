<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useTeams } from '@/pages/teams/composables/use-teams.composable'
import { useUsers } from '@/pages/users/composables/use-users.composable'

import TeamCreate from './components/team-create-dialog.vue'
import TeamsList from './components/teams-list.vue'
import UsersList from './components/users-list.vue'

const {
  loading: teamsLoading,
  teams,
  fetchTeamsData,
  addUsersToTeam,
  isAddingUsers,
} = useTeams()

const { loading: usersLoading, users, fetchUsersData } = useUsers()

const loadingTeamId = ref<number | null>(null)
const isDragging = ref(false)

// Fetch data on mount
onMounted(() => {
  fetchTeamsData()
  fetchUsersData()
})

async function handleUserDropped(teamId: number, userId: number) {
  try {
    loadingTeamId.value = teamId
    await addUsersToTeam(teamId, [userId])
    // Refetch teams and users to update the UI
    await Promise.all([fetchTeamsData(), fetchUsersData()])
  } catch (error) {
    // Error is already handled in the composable
  } finally {
    loadingTeamId.value = null
  }
}

function handleDragEnter(teamId: number) {
  isDragging.value = true
}

function handleDragLeave(teamId: number) {
  // Only reset if not dragging over another team
  setTimeout(() => {
    isDragging.value = false
  }, 100)
}

const loading = computed(() => teamsLoading.value || usersLoading.value)
</script>

<template>
  <Page
    title="Teams"
    description="Drag and drop users to add them to teams"
    sticky
    data-testid="teams_page"
  >
    <template #actions>
      <TeamCreate />
    </template>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 h-full">
      <!-- Left Column: Teams -->
      <div class="space-y-4">
        <TeamsList
          :teams="teams"
          :loading="teamsLoading"
          :is-dragging="isDragging"
          :loading-team-id="loadingTeamId"
          @user-dropped="handleUserDropped"
          @drag-enter="handleDragEnter"
          @drag-leave="handleDragLeave"
        />
      </div>

      <!-- Right Column: Users -->
      <div class="space-y-4">
        <UsersList :users="users" :loading="usersLoading" />
      </div>
    </div>
  </Page>
</template>
