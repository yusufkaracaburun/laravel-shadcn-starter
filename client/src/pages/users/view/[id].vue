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
  LayoutGridIcon,
  ShieldIcon,
  UserCircleIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { useUsers } from '@/composables/use-users.composable'

import UserAccountStatusCard from './components/user-account-status-card.vue'
import UserHeader from './components/user-header.vue'
import UserNavbar from './components/user-navbar.vue'
import UserProfileCard from './components/user-profile-card.vue'
import UserRolesCard from './components/user-roles-card.vue'
import UserTeamsCard from './components/user-teams-card.vue'
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

const isEmailVerified = computed(() => {
  if (!user.value) {
    return false
  }
  return !!user.value.email_verified_at
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

const formattedEmailVerifiedAt = computed(() => {
  if (!user.value?.email_verified_at) {
    return null
  }

  return new Date(user.value.email_verified_at).toLocaleString('en-US', {
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
          <!-- Enhanced Header Section -->
          <div
            class="relative overflow-hidden rounded-xl border bg-gradient-to-br from-background to-muted/20 p-8 shadow-sm"
          >
            <div class="relative z-10">
              <UserHeader
                :user="user"
                :initials="userInitials"
                :is-email-verified="isEmailVerified"
              />
            </div>
          </div>

          <!-- Modern Tabs Section -->
          <div class="space-y-6">
            <Tabs v-model="activeTab" class="w-full">
              <TabsList
                class="h-auto w-full justify-start gap-1 bg-muted/50 p-1"
              >
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
              </TabsList>

              <TabsContent value="overview" class="mt-8">
                <div class="grid gap-6 lg:grid-cols-2">
                  <UserProfileCard
                    :user="user"
                    :is-email-verified="isEmailVerified"
                    :email-verified-at="formattedEmailVerifiedAt"
                  />

                  <UserAccountStatusCard
                    :user="user"
                    :created-at="formattedCreatedAt"
                    :updated-at="formattedUpdatedAt"
                  />
                </div>
              </TabsContent>

              <TabsContent value="account" class="mt-8">
                <div class="max-w-2xl">
                  <UserAccountStatusCard
                    :user="user"
                    :created-at="formattedCreatedAt"
                    :updated-at="formattedUpdatedAt"
                  />
                </div>
              </TabsContent>

              <TabsContent value="roles" class="mt-8">
                <div class="max-w-2xl">
                  <UserRolesCard :user="user" />
                </div>
              </TabsContent>

              <TabsContent value="teams" class="mt-8">
                <div class="max-w-2xl">
                  <UserTeamsCard :user="user" />
                </div>
              </TabsContent>
            </Tabs>
          </div>
        </div>
      </template>
    </UserViewLayout>
  </Page>
</template>
