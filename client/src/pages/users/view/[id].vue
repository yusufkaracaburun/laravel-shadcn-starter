<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, ref } from 'vue'

import type { IUser } from '@/pages/users/models/users'

import Page from '@/components/global-layout/basic-page.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  FileTextIcon,
  LayoutGridIcon,
  ShieldIcon,
  UserCircleIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { useUsers } from '@/pages/users/composables/use-users.composable'

import UserAccountStatusCard from './components/user-account-status-card.vue'
import UserActivityCard from './components/user-activity-card.vue'
import UserConnectionsCard from './components/user-connections-card.vue'
import UserInvoicesCard from './components/user-invoices-card.vue'
import UserNavbar from './components/user-navbar.vue'
import UserProfileCompletionCard from './components/user-profile-completion-card.vue'
import UserProfileSummaryCard from './components/user-profile-summary-card.vue'
import UserRolesCard from './components/user-roles-card.vue'
import UserSkillsCard from './components/user-skills-card.vue'
import UserTeamsCard from './components/user-teams-card.vue'
import UserTransactionsCard from './components/user-transactions-card.vue'
import UserViewLayout from './components/user-view-layout.vue'

// Composables
const {
  userByIdResponse,
  isLoadingUserById,
  isErrorUserById,
  errorUserById,
  fetchUserByIdData,
} = useUsers()

// Computed properties
const user = computed<IUser | null>(() => {
  const data = userByIdResponse.value?.data
  // Handle case where data might be an array (shouldn't happen, but type safety)
  if (Array.isArray(data)) {
    return data[0] ?? null
  }
  return data ?? null
})

const pageTitle = computed(() => user.value?.name ?? 'User Details')

const pageDescription = computed(() =>
  user.value
    ? `View details for ${user.value.email}`
    : 'Loading user information...',
)

// Computed values for components
const userInitials = computed(() => {
  if (!user.value) {
    return '?'
  }

  const name = user.value.name
  if (!name || name === '—') {
    return '?'
  }

  const parts = name.trim().split(/\s+/)

  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }

  return name[0].toUpperCase()
})

const formattedCreatedAt = computed(() => {
  if (!user.value?.created_at) {
    return '—'
  }

  return new Date(user.value.created_at).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

const formattedUpdatedAt = computed(() => {
  if (!user.value?.updated_at) {
    return '—'
  }

  return new Date(user.value.updated_at).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

// Tab state
const activeTab = ref('overview')

// Event handlers
function handleEditClosed() {
  fetchUserByIdData()
}

function handleDeleteClosed() {
  // User will be redirected by the navbar component
}
</script>

<template>
  <Page :title="pageTitle" :description="pageDescription">
    <template #actions>
      <UserNavbar
        v-if="user"
        :user="user"
        @edit-closed="handleEditClosed"
        @delete-closed="handleDeleteClosed"
      />
    </template>

    <UserViewLayout
      :is-loading="isLoadingUserById"
      :is-error="isErrorUserById"
      :error-object="errorUserById"
      :on-retry="fetchUserByIdData"
    >
      <template v-if="user">
        <div class="space-y-8">
          <!-- Modern Tabs Section -->
          <div class="space-y-6">
            <Tabs v-model="activeTab" class="w-full">
              <TabsList class="h-auto justify-start gap-1 bg-muted/50 p-1">
                <TabsTrigger
                  value="overview"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <LayoutGridIcon class="size-4" />
                  <span>Overview</span>
                </TabsTrigger>
                <TabsTrigger
                  value="account"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <UserCircleIcon class="size-4" />
                  <span>Account</span>
                </TabsTrigger>
                <TabsTrigger
                  value="roles"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <ShieldIcon class="size-4" />
                  <span>Roles & Permissions</span>
                </TabsTrigger>
                <TabsTrigger
                  value="teams"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <UsersIcon class="size-4" />
                  <span>Teams</span>
                </TabsTrigger>
                <TabsTrigger
                  value="invoices"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <FileTextIcon class="size-4" />
                  <span>Invoices</span>
                </TabsTrigger>
              </TabsList>

              <div class="mt-8 grid gap-6 grid-cols-3">
                <!-- Fixed Left Column (1/3 width) -->
                <div class="space-y-6">
                  <UserProfileSummaryCard
                    :user="user"
                    :initials="userInitials"
                  />
                  <div v-if="activeTab === 'overview'" class="space-y-6">
                    <UserProfileCompletionCard />
                    <UserSkillsCard />
                  </div>
                </div>

                <!-- Right Column (2/3 width) - Tab Content -->
                <div class="space-y-6 col-span-2">
                  <TabsContent value="overview" class="mt-0">
                    <div class="space-y-6">
                      <UserActivityCard />
                      <div class="grid gap-6 grid-cols-2">
                        <UserTransactionsCard />
                        <UserConnectionsCard />
                      </div>
                    </div>
                  </TabsContent>

                  <TabsContent value="account" class="mt-0">
                    <UserAccountStatusCard
                      :user="user"
                      :created-at="formattedCreatedAt"
                      :updated-at="formattedUpdatedAt"
                    />
                  </TabsContent>

                  <TabsContent value="roles" class="mt-0">
                    <UserRolesCard :user="user" />
                  </TabsContent>

                  <TabsContent value="teams" class="mt-0">
                    <UserTeamsCard :user="user" />
                  </TabsContent>

                  <TabsContent value="invoices" class="mt-0">
                    <UserInvoicesCard :user="user" />
                  </TabsContent>
                </div>
              </div>
            </Tabs>
          </div>
        </div>
      </template>
    </UserViewLayout>
  </Page>
</template>
