<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import {
  ArrowLeft,
  Calendar,
  FilePenLine,
  FolderKanban,
  Target,
  Trash2,
} from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { StatusBadge } from '@/components/ui/status-badge'
import { useGetProjectQuery } from '@/services/projects.service'

import type { Project } from './data/schema'

import ProjectDelete from './components/project-delete.vue'
import ProjectResourceDialog from './components/project-resource-dialog.vue'
import { categories, statuses } from './data/data'

const route = useRoute()
const router = useRouter()

const projectId = computed(() => Number(route.params.id))

const {
  data: projectResponse,
  isLoading,
  isError,
  error,
  refetch,
} = useGetProjectQuery(projectId)

const project = computed<Project | null>(
  () => projectResponse.value?.data ?? null,
)

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

// Get status info
function getStatusInfo(status: string) {
  return statuses.find((s) => s.value === status) || null
}

// Get category label
function getCategoryLabel(category: string) {
  return categories.find((c) => c.value === category)?.label || category
}

// Handle edit
const editDialogOpen = ref(false)

function handleEditClose() {
  editDialogOpen.value = false
  // Refetch project data after edit
  refetch()
}

// Handle delete
const deleteDialogOpen = ref(false)

function handleDeleteClose() {
  deleteDialogOpen.value = false
  // Navigate back to projects list after deletion
  router.push('/projects')
}

// Check if error is 404
const isNotFound = computed(() => {
  if (!isError.value || !error.value) return false
  return (error.value as any)?.response?.status === 404
})
</script>

<template>
  <Page
    :title="project ? project.name : 'Project Details'"
    :description="
      project
        ? `View details for ${project.name}`
        : 'Loading project information...'
    "
  >
    <template #actions>
      <div v-if="project" class="flex items-center gap-2">
        <Button variant="outline" @click="router.push('/projects')">
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
        subtitle="Project Not Found"
        error="The project you are looking for might have been removed or doesn't exist."
      />
    </div>

    <div
      v-else-if="isError"
      class="flex items-center justify-center min-h-[400px]"
    >
      <div class="text-center">
        <Error
          :code="500"
          subtitle="Error Loading Project"
          error="An error occurred while loading the project information. Please try again."
        />
        <Button class="mt-4" @click="refetch()"> Retry </Button>
      </div>
    </div>

    <div v-else-if="project" class="space-y-6">
      <!-- Header Section -->
      <Card>
        <CardHeader>
          <div class="flex items-start gap-6">
            <div
              class="flex size-24 items-center justify-center rounded-full bg-primary/10"
            >
              <FolderKanban class="size-12 text-primary" />
            </div>
            <div class="flex-1">
              <CardTitle class="text-3xl mb-2">
                {{ project.name }}
              </CardTitle>
              <CardDescription v-if="project.description" class="text-base">
                {{ project.description }}
              </CardDescription>
              <div class="mt-4 flex items-center gap-2 flex-wrap">
                <Badge
                  v-if="getStatusInfo(project.status)"
                  :class="getStatusInfo(project.status)!.color"
                  variant="secondary"
                >
                  <component
                    :is="getStatusInfo(project.status)!.icon"
                    class="mr-1 size-3"
                  />
                  {{ getStatusInfo(project.status)!.label }}
                </Badge>
                <Badge variant="outline">
                  {{ getCategoryLabel(project.category) }}
                </Badge>
              </div>
            </div>
          </div>
        </CardHeader>
      </Card>

      <!-- Information Grid -->
      <div class="grid gap-6 md:grid-cols-2">
        <!-- Project Information -->
        <Card>
          <CardHeader>
            <CardTitle>Project Information</CardTitle>
            <CardDescription>Basic project details</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Name
              </div>
              <div class="text-base">
                {{ project.name }}
              </div>
            </div>
            <div v-if="project.description">
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Description
              </div>
              <div class="text-base">
                {{ project.description }}
              </div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Category
              </div>
              <div class="text-base">
                <Badge variant="outline">
                  {{ getCategoryLabel(project.category) }}
                </Badge>
              </div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Status
              </div>
              <div class="text-base">
                <StatusBadge
                  v-if="getStatusInfo(project.status)"
                  :status="project.status"
                  type="project"
                  :icon="getStatusInfo(project.status)!.icon"
                  :label="getStatusInfo(project.status)!.label"
                />
              </div>
            </div>
            <div>
              <div
                class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
              >
                <Target class="size-4" />
                Progress
              </div>
              <div class="text-base">
                <div class="flex items-center gap-2">
                  <div class="flex-1 bg-secondary rounded-full h-2">
                    <div
                      class="bg-primary h-2 rounded-full transition-all"
                      :style="{ width: `${project.progress}%` }"
                    />
                  </div>
                  <span class="text-sm font-medium"
                    >{{ project.progress }}%</span
                  >
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Timeline & Dates -->
        <Card>
          <CardHeader>
            <CardTitle>Timeline & Dates</CardTitle>
            <CardDescription>
              Project schedule and timeline information
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <div
                class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
              >
                <Calendar class="size-4" />
                Start Date
              </div>
              <div class="text-base">
                {{ formatDate(project.start_date || project.startDate) }}
              </div>
            </div>
            <div>
              <div
                class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
              >
                <Calendar class="size-4" />
                End Date
              </div>
              <div class="text-base">
                {{ formatDate(project.end_date || project.endDate) }}
              </div>
            </div>
            <div>
              <div
                class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
              >
                <Calendar class="size-4" />
                Created At
              </div>
              <div class="text-base">
                {{ formatDateTime(project.created_at) }}
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
                {{ formatDateTime(project.updated_at) }}
              </div>
            </div>
            <div v-if="project.team_id">
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Team ID
              </div>
              <div class="text-base">
                {{ project.team_id }}
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Edit Dialog -->
    <UiDialog v-if="project" v-model:open="editDialogOpen">
      <UiDialogContent>
        <ProjectResourceDialog :project="project" @close="handleEditClose" />
      </UiDialogContent>
    </UiDialog>

    <!-- Delete Dialog -->
    <UiDialog v-if="project" v-model:open="deleteDialogOpen">
      <UiDialogContent>
        <ProjectDelete :project="project" @close="handleDeleteClose" />
      </UiDialogContent>
    </UiDialog>
  </Page>
</template>
