<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import {
  ArrowLeft,
  Calendar,
  FilePenLine,
  Mail,
  Shield,
  Trash2,
  Users,
} from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import type { IUser } from '@/pages/users/models/users'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { useUserService } from '@/services/users.service'

import UserDelete from '../components/user-delete.vue'
import UserResourceDialog from '../components/user-resource-dialog.vue'

const route = useRoute()
const router = useRouter()

const userService = useUserService()
const userId = computed(() => {
  const idParam = route.params.id
  if (
    !idParam ||
    typeof idParam !== 'string' ||
    Number.isNaN(Number(idParam))
  ) {
    return undefined
  }
  return Number(idParam)
})

const {
  data: userResponse,
  isLoading,
  isError,
  error,
  refetch,
} = userService.getUserByIdQuery(userId)

const user = computed<IUser | null>(() => userResponse.value?.data ?? null)

// Get initials from name
function getInitials(name: string): string {
  if (!name || name === '—') return '?'
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name[0].toUpperCase()
}

// Format date
function formatDate(dateString: string | null): string {
  if (!dateString) return '—'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

// Format datetime
function formatDateTime(dateString: string | null): string {
  if (!dateString) return '—'
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Handle edit
const editDialogOpen = ref(false)

function handleEditClose() {
  editDialogOpen.value = false
  // Refetch user data after edit
  refetch()
}

// Handle delete
const deleteDialogOpen = ref(false)

function handleDeleteClose() {
  deleteDialogOpen.value = false
  // Navigate back to users list after deletion
  router.push('/users')
}

// Check if error is 404
const isNotFound = computed(() => {
  if (!isError.value || !error.value) return false
  return (error.value as any)?.response?.status === 404
})
</script>

<template>
  <Page
    :title="user ? user.name : 'User Details'"
    :description="
      user ? `View details for ${user.email}` : 'Loading user information...'
    "
  >
    <template #actions>
      <div v-if="user" class="flex items-center gap-2">
        <Button variant="outline" @click="router.push('/users')">
          <ArrowLeft class="mr-2 size-4" />
          Back
        </Button>
        <Button variant="outline" @click="editDialogOpen = true">
          <FilePenLine class="mr-2 size-4" />
          Edit
        </Button>
        <Button variant="destructive" @click="deleteDialogOpen = true">
          <Trash2 class="mr-2 size-4" />
          Delete
        </Button>
      </div>
    </template>

    <div
      v-if="isLoading"
      class="flex items-center justify-center min-h-[400px]"
    >
      <Loading />
    </div>

    <div
      v-else-if="isError && isNotFound"
      class="flex items-center justify-center min-h-[400px]"
    >
      <Error
        :code="404"
        subtitle="User Not Found"
        error="The user you are looking for might have been removed or doesn't exist."
      />
    </div>

    <div
      v-else-if="isError"
      class="flex items-center justify-center min-h-[400px]"
    >
      <div class="text-center">
        <Error
          :code="500"
          subtitle="Error Loading User"
          error="An error occurred while loading the user information. Please try again."
        />
        <Button class="mt-4" @click="refetch()"> Retry </Button>
      </div>
    </div>

    <div v-else-if="user" class="space-y-6">
      <!-- Header Section -->
      <Card>
        <CardHeader>
          <div class="flex items-start gap-6">
            <Avatar class="size-24">
              <AvatarImage
                v-if="user.profile_photo_url"
                :src="user.profile_photo_url"
                :alt="user.name"
              />
              <AvatarFallback class="text-2xl">
                {{ getInitials(user.name) }}
              </AvatarFallback>
            </Avatar>
            <div class="flex-1">
              <CardTitle class="text-3xl mb-2">
                {{ user.name }}
              </CardTitle>
              <CardDescription class="text-base flex items-center gap-2">
                <Mail class="size-4" />
                {{ user.email }}
              </CardDescription>
              <div class="mt-4 flex items-center gap-2">
                <Badge
                  :variant="user.email_verified_at ? 'default' : 'secondary'"
                >
                  {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                </Badge>
              </div>
            </div>
          </div>
        </CardHeader>
      </Card>

      <!-- Information Grid -->
      <div class="grid gap-6 md:grid-cols-2">
        <!-- Profile Information -->
        <Card>
          <CardHeader>
            <CardTitle>Profile Information</CardTitle>
            <CardDescription>Basic user profile details</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Name
              </div>
              <div class="text-base">
                {{ user.name }}
              </div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Email
              </div>
              <div class="text-base">
                {{ user.email }}
              </div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Email Verification
              </div>
              <div class="text-base">
                <Badge
                  :variant="user.email_verified_at ? 'default' : 'secondary'"
                >
                  {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                </Badge>
                <span
                  v-if="user.email_verified_at"
                  class="ml-2 text-sm text-muted-foreground"
                >
                  {{ formatDateTime(user.email_verified_at) }}
                </span>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Account Status -->
        <Card>
          <CardHeader>
            <CardTitle>Account Status</CardTitle>
            <CardDescription>
              Account creation and update information
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <div
                class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
              >
                <Calendar class="size-4" />
                Created At
              </div>
              <div class="text-base">
                {{ formatDateTime(user.created_at) }}
              </div>
            </div>
            <div>
              <div
                class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
              >
                <Calendar class="size-4" />
                Updated At
              </div>
              <div class="text-base">
                {{ formatDateTime(user.updated_at) }}
              </div>
            </div>
            <div v-if="user.current_team_id">
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Current Team ID
              </div>
              <div class="text-base">
                {{ user.current_team_id }}
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Roles & Permissions -->
        <Card v-if="user.roles && user.roles.length > 0">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Shield class="size-5" />
              Roles & Permissions
            </CardTitle>
            <CardDescription>Assigned roles for this user</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="flex flex-wrap gap-2">
              <Badge
                v-for="role in user.roles"
                :key="role.id"
                variant="outline"
              >
                {{ role.name }}
              </Badge>
            </div>
          </CardContent>
        </Card>

        <!-- Teams -->
        <Card v-if="user.teams && user.teams.length > 0">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Users class="size-5" />
              Teams
            </CardTitle>
            <CardDescription>Teams this user belongs to</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-2">
              <div
                v-for="team in user.teams"
                :key="team.id"
                class="flex items-center justify-between p-3 rounded-lg border"
              >
                <div>
                  <div class="font-medium">
                    {{ team.name }}
                  </div>
                  <div class="text-sm text-muted-foreground">
                    {{ team.personal_team ? 'Personal Team' : 'Team' }}
                  </div>
                </div>
                <Badge
                  v-if="user.currentTeam && user.currentTeam.id === team.id"
                  variant="default"
                >
                  Current
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Edit Dialog -->
    <UiDialog v-if="user" v-model:open="editDialogOpen">
      <UiDialogContent>
        <UserResourceDialog :user="user" @close="handleEditClose" />
      </UiDialogContent>
    </UiDialog>

    <!-- Delete Dialog -->
    <UiDialog v-if="user" v-model:open="deleteDialogOpen">
      <UiDialogContent>
        <UserDelete :user="user" @close="handleDeleteClose" />
      </UiDialogContent>
    </UiDialog>
  </Page>
</template>
