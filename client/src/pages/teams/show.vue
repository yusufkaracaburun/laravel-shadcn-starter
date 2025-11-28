<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

import ActionSection from '@/components/action-section.vue'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const authStore = useAuthStore()

const teamId = computed(() => Number(route.params.team))
const user = computed(() => authStore.user)
const team = computed(() => {
  if (!user.value?.teams)
    return null
  return user.value.teams.find(t => t.id === teamId.value) || user.value.currentTeam
})
</script>

<template>
  <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="mb-6">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
        Team Settings
      </h2>
    </div>

    <div v-if="!team" class="text-center py-8 text-muted-foreground">
      Team not found.
    </div>

    <div v-else class="space-y-6">
      <ActionSection>
        <template #title>
          Team Information
        </template>

        <template #description>
          View and manage your team's information.
        </template>

        <template #content>
          <div class="space-y-4">
            <div>
              <p class="text-sm font-medium text-muted-foreground">Team Name</p>
              <p class="text-lg font-semibold">{{ team.name }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">Team ID</p>
              <p class="text-lg">{{ team.id }}</p>
            </div>
            <div>
              <UiBadge :variant="team.personal_team ? 'secondary' : 'default'">
                {{ team.personal_team ? 'Personal Team' : 'Team' }}
              </UiBadge>
            </div>
          </div>
        </template>
      </ActionSection>

      <!-- Note: Team member management and other features would go here -->
      <!-- These may require additional backend API endpoints -->
    </div>
  </div>
</template>

