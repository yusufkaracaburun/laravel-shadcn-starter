<script lang="ts" setup>
import { Donut } from '@unovis/ts'
import { VisDonut, VisSingleContainer } from '@unovis/vue'

import type { ChartConfig } from '@/components/ui/chart'

import {
  ChartContainer,
  ChartLegendContent,
  ChartTooltip,
  ChartTooltipContent,
  componentToString,
} from '@/components/ui/chart'
import { Progress } from '@/components/ui/progress'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import projects from '@/pages/projects/data/projects.json'

const timeRange = ref('all')

const filteredProjects = computed(() => {
  if (timeRange.value === 'all') {
    return projects
  }

  const now = new Date()
  let daysToSubtract = 0

  if (timeRange.value === '90d') {
    daysToSubtract = 90
  } else if (timeRange.value === '30d') {
    daysToSubtract = 30
  } else if (timeRange.value === '7d') {
    daysToSubtract = 7
  }

  const startDate = new Date(now)
  startDate.setDate(startDate.getDate() - daysToSubtract)

  return projects.filter((project) => {
    const projectStartDate = new Date(project.startDate)
    return projectStartDate >= startDate
  })
})

const totalProjects = computed(() => filteredProjects.value.length)
const activeProjects = computed(
  () => filteredProjects.value.filter((p) => p.status === 'active').length,
)
const completedProjects = computed(
  () => filteredProjects.value.filter((p) => p.status === 'completed').length,
)
const inProgressProjects = computed(() => {
  return filteredProjects.value.filter(
    (p) => p.status === 'active' && p.progress > 0 && p.progress < 100,
  ).length
})

const recentProjects = computed(() => {
  return filteredProjects.value
    .filter((p) => p.status === 'active')
    .sort((a, b) => new Date(b.startDate).getTime() - new Date(a.startDate).getTime())
    .slice(0, 5)
})

interface PieChartData {
  status: string
  value: number
  fill: string
}

function getChartColor(index: number) {
  if (globalThis.window === undefined) return `var(--chart-${index})`
  const root = document.documentElement
  const value = getComputedStyle(root).getPropertyValue(`--chart-${index}`).trim()
  return value || `var(--chart-${index})`
}

const pieChartData = computed<PieChartData[]>(() => {
  const onHoldCount = filteredProjects.value.filter((p) => p.status === 'on-hold').length
  const cancelledCount = filteredProjects.value.filter((p) => p.status === 'cancelled').length

  const data = [
    { status: 'active', value: activeProjects.value, fill: getChartColor(1) },
    { status: 'completed', value: completedProjects.value, fill: getChartColor(2) },
    { status: 'onHold', value: onHoldCount, fill: getChartColor(3) },
    { status: 'cancelled', value: cancelledCount, fill: getChartColor(4) },
  ]

  return data.filter((item) => item.value > 0)
})

const totalProjectsCount = computed(() =>
  pieChartData.value.reduce((acc, curr) => acc + curr.value, 0),
)

type Data = (typeof pieChartData.value)[number]

const chartConfig = {
  active: {
    label: 'Active',
    color: 'var(--chart-1)',
  },
  completed: {
    label: 'Completed',
    color: 'var(--chart-2)',
  },
  onHold: {
    label: 'On Hold',
    color: 'var(--chart-3)',
  },
  cancelled: {
    label: 'Cancelled',
    color: 'var(--chart-4)',
  },
} satisfies ChartConfig

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
        <UiCardTitle class="text-sm font-medium"> Total Projects </UiCardTitle>
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
        <p class="text-xs text-muted-foreground">All projects in system</p>
      </UiCardContent>
    </UiCard>
    <UiCard data-testid="projects-content_active-projects_card">
      <UiCardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
        <UiCardTitle class="text-sm font-medium"> Active Projects </UiCardTitle>
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
        <UiCardTitle class="text-sm font-medium"> Completed Projects </UiCardTitle>
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
        <UiCardTitle class="text-sm font-medium"> In Progress </UiCardTitle>
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
        <p class="text-xs text-muted-foreground">Currently being worked on</p>
      </UiCardContent>
    </UiCard>
  </div>

  <div class="grid grid-cols-1 gap-4 lg:grid-cols-7">
    <UiCard class="col-span-1 lg:col-span-4" data-testid="projects-content_status-card">
      <UiCardHeader class="flex items-center gap-2 space-y-0 border-b py-5 sm:flex-row">
        <div class="grid flex-1 gap-1">
          <UiCardTitle>Project Status Distribution</UiCardTitle>
          <UiCardDescription> Overview of projects by status </UiCardDescription>
        </div>
        <Select v-model="timeRange" data-testid="projects-content_time-range_select">
          <SelectTrigger
            class="hidden w-[160px] rounded-lg sm:ml-auto sm:flex"
            aria-label="Select time range"
          >
            <SelectValue placeholder="All Projects" />
          </SelectTrigger>
          <SelectContent class="rounded-xl">
            <SelectItem value="all" class="rounded-lg"> All Projects </SelectItem>
            <SelectItem value="90d" class="rounded-lg"> Last 3 months </SelectItem>
            <SelectItem value="30d" class="rounded-lg"> Last 30 days </SelectItem>
            <SelectItem value="7d" class="rounded-lg"> Last 7 days </SelectItem>
          </SelectContent>
        </Select>
      </UiCardHeader>
      <UiCardContent class="px-2 pt-4 sm:px-6 sm:pt-6 pb-4">
        <ChartContainer
          :config="chartConfig"
          class="mx-auto aspect-square max-h-[250px] w-full"
          :cursor="false"
          :style="{
            '--vis-donut-central-label-font-size': 'var(--text-3xl)',
            '--vis-donut-central-label-font-weight': 'var(--font-weight-bold)',
            '--vis-donut-central-label-text-color': 'var(--foreground)',
            '--vis-donut-central-sub-label-text-color': 'var(--muted-foreground)',
          }"
          data-testid="projects-content_status-chart"
        >
          <VisSingleContainer :data="pieChartData" :margin="{ top: 30, bottom: 30 }">
            <VisDonut
              :value="(d: Data) => d.value"
              :color="
                (d: Data) => chartConfig[d.status as keyof typeof chartConfig]?.color || d.fill
              "
              :arc-width="30"
              :central-label-offset-y="10"
              :central-label="totalProjectsCount.toLocaleString()"
              central-sub-label="Projects"
            />
            <ChartTooltip
              :triggers="{
                [Donut.selectors.segment]: componentToString(chartConfig, ChartTooltipContent, {
                  hideLabel: true,
                })!,
              }"
            />
          </VisSingleContainer>
          <ChartLegendContent />
        </ChartContainer>
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
