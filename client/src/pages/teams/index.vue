<script setup lang="ts">
import Page from '@/components/global-layout/basic-page.vue'
import { useTeams } from '@/pages/teams/composables/use-teams.composable'
import { useUsers } from '@/pages/users/composables/use-users.composable'
import { EUserStatus } from '@/pages/users/models/users'

import TeamCreate from './components/team-create-dialog.vue'
import TeamsList from './components/teams-list.vue'
import UsersList from './components/users-list.vue'

const {
  loading: teamsLoading,
  teams,
  fetchTeamsData,
  addUsersToTeam,
  pageSize: teamsPageSize,
} = useTeams()

const {
  loading: usersLoading,
  users: allUsers,
  fetchUsersData,
  pageSize: usersPageSize,
  filter: usersFilter,
  onFiltersChange: onUsersFiltersChange,
} = useUsers()

// Filter users to only show active and registered status
const users = computed(() => {
  return allUsers.value.filter(
    (user) =>
      user.status === EUserStatus.ACTIVE ||
      user.status === EUserStatus.REGISTERED,
  )
})

const loadingTeamId = ref<number | null>(null)
const isDragging = ref(false)

// Set high page size to fetch all items
// Teams are already configured to fetch with members (defaultIncludeKey: 'members')
onMounted(() => {
  // Set page size to a very high number to fetch all teams and users
  teamsPageSize.value = 99999
  usersPageSize.value = 99999

  // Fetch all users, then filter to active and registered on frontend
  onUsersFiltersChange({ ...usersFilter.value })

  fetchTeamsData()
  fetchUsersData()
})

async function handleUserDropped(teamId: number, userId: number) {
  try {
    loadingTeamId.value = teamId
    await addUsersToTeam(teamId, [userId])
    // Refetch teams and users to update the UI
    onUsersFiltersChange({ ...usersFilter.value })
    await Promise.all([fetchTeamsData(), fetchUsersData()])
  } catch {
    // Error is already handled in the composable
  } finally {
    loadingTeamId.value = null
  }
}

function handleDragEnter(_teamId: number) {
  isDragging.value = true
}

function handleDragLeave(_teamId: number) {
  // Only reset if not dragging over another team
  setTimeout(() => {
    isDragging.value = false
  }, 100)
}
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
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 h-full">
      <!-- Left Column: Teams -->
      <div class="space-y-4 lg:col-span-3">
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
      <div class="space-y-4 lg:col-span-2">
        <UsersList :users="users" :loading="usersLoading" />
      </div>
    </div>
  </Page>
</template>
