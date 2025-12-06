<script lang="ts" setup>
import { Progress } from '@/components/ui/progress'
import projects from '@/pages/projects/data/projects.json'

const totalProjects = computed(() => projects.length)
const activeProjects = computed(() => projects.filter(p => p.status === 'active').length)
const completedProjects = computed(() => projects.filter(p => p.status === 'completed').length)
const inProgressProjects = computed(() => {
  return projects.filter(p => p.status === 'active' && p.progress > 0 && p.progress < 100).length
})

const recentProjects = computed(() => {
  return projects
    .filter(p => p.status === 'active')
    .sort((a, b) => new Date(b.startDate).getTime() - new Date(a.startDate).getTime())
    .slice(0, 5)
})

interface StatusData {
  status: string
  value: number
  color: string
  percentage: number
}

const statusDistributionData = computed<StatusData[]>(() => {
  const onHoldCount = projects.filter(p => p.status === 'on-hold').length
  const cancelledCount = projects.filter(p => p.status === 'cancelled').length

  const data = [
    { status: 'Active', value: activeProjects.value, color: 'bg-blue-500' },
    { status: 'Completed', value: completedProjects.value, color: 'bg-green-500' },
    { status: 'On Hold', value: onHoldCount, color: 'bg-yellow-500' },
    { status: 'Cancelled', value: cancelledCount, color: 'bg-red-500' },
  ]

  const maxValue = Math.max(...data.map(d => d.value), 1)

  return data.map(item => ({
    ...item,
    percentage: totalProjects.value > 0 ? Math.round((item.value / totalProjects.value) * 100) : 0,
    widthPercentage: (item.value / maxValue) * 100,
  }))
})

function formatDate(dateString: string) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-testid="projects-content_metrics_grid">
    <UiCard data-testid="projects-content_total-projects_card">
      <UiCardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
        <UiCardTitle class="text-sm font-medium">
          Total Projects
        </UiCardTitle>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth="2"
          class="size-4 text-muted-foreground"
        >
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
          <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
        </svg>
      </UiCardHeader>
      <UiCardContent>
        <div class="text-2xl font-bold">
          {{ totalProjects }}
        </div>
        <p class="text-xs text-muted-foreground">
          All projects in system
        </p>
      </UiCardContent>
    </UiCard>
    <UiCard data-testid="projects-content_active-projects_card">
      <UiCardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
        <UiCardTitle class="text-sm font-medium">
          Active Projects
        </UiCardTitle>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth="2"
          class="size-4 text-muted-foreground"
        >
          <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
        </svg>
      </UiCardHeader>
      <UiCardContent>
        <div class="text-2xl font-bold">
          {{ activeProjects }}
        </div>
        <p class="text-xs text-muted-foreground">
          {{ Math.round((activeProjects / totalProjects) * 100) }}% of total
        </p>
      </UiCardContent>
    </UiCard>
    <UiCard data-testid="projects-content_completed-projects_card">
      <UiCardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
        <UiCardTitle class="text-sm font-medium">
          Completed Projects
        </UiCardTitle>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth="2"
          class="size-4 text-muted-foreground"
        >
          <path d="M20 6 9 17l-5-5" />
        </svg>
      </UiCardHeader>
      <UiCardContent>
        <div class="text-2xl font-bold">
          {{ completedProjects }}
        </div>
        <p class="text-xs text-muted-foreground">
          {{ Math.round((completedProjects / totalProjects) * 100) }}% of total
        </p>
      </UiCardContent>
    </UiCard>
    <UiCard data-testid="projects-content_in-progress_card">
      <UiCardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
        <UiCardTitle class="text-sm font-medium">
          In Progress
        </UiCardTitle>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeLinecap="round"
          strokeLinejoin="round"
          strokeWidth="2"
          class="size-4 text-muted-foreground"
        >
          <circle cx="12" cy="12" r="10" />
          <polyline points="12 6 12 12 16 14" />
        </svg>
      </UiCardHeader>
      <UiCardContent>
        <div class="text-2xl font-bold">
          {{ inProgressProjects }}
        </div>
        <p class="text-xs text-muted-foreground">
          Currently being worked on
        </p>
      </UiCardContent>
    </UiCard>
  </div>

  <div class="grid grid-cols-1 gap-4 lg:grid-cols-7">
    <UiCard class="col-span-1 lg:col-span-4" data-testid="projects-content_status-card">
      <UiCardHeader>
        <UiCardTitle>Project Status Distribution</UiCardTitle>
        <UiCardDescription> Overview of projects by status </UiCardDescription>
      </UiCardHeader>
      <UiCardContent class="px-2 pt-4 sm:px-6 sm:pt-6 pb-4">
        <div class="space-y-6" data-testid="projects-content_status-chart">
          <div
            v-for="item in statusDistributionData"
            :key="item.status"
            class="space-y-2"
          >
            <div class="flex items-center justify-between text-sm">
              <span class="font-medium">{{ item.status }}</span>
              <div class="flex items-center gap-2">
                <span class="font-bold">{{ item.value }}</span>
                <span class="text-muted-foreground">({{ item.percentage }}%)</span>
              </div>
            </div>
            <div class="relative h-8 w-full overflow-hidden rounded-md bg-muted">
              <div
                :class="item.color"
                class="h-full rounded-md transition-all duration-500"
                :style="{ width: `${item.widthPercentage}%` }"
                data-testid="projects-content_status-bar"
              />
            </div>
          </div>
        </div>
      </UiCardContent>
    </UiCard>
    <UiCard class="col-span-1 lg:col-span-3" data-testid="projects-content_recent-projects_card">
      <UiCardHeader>
        <UiCardTitle>Recent Projects</UiCardTitle>
        <UiCardDescription> Latest active projects </UiCardDescription>
      </UiCardHeader>
      <UiCardContent>
        <div class="space-y-4">
          <div
            v-for="project in recentProjects"
            :key="project.id"
            class="flex items-center justify-between"
            data-testid="projects-content_recent-project-item"
          >
            <div class="space-y-1">
              <p class="text-sm font-medium">
                {{ project.name }}
              </p>
              <p class="text-xs text-muted-foreground">
                {{ formatDate(project.startDate) }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <Progress :model-value="project.progress" class="h-2 w-20" />
              <span class="text-xs text-muted-foreground">{{ project.progress }}%</span>
            </div>
          </div>
        </div>
      </UiCardContent>
    </UiCard>
  </div>
</template>
