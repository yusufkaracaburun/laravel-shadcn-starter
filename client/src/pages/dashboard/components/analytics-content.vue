<script lang="ts" setup>
import { VisArea, VisAxis, VisLine, VisXYContainer } from '@unovis/vue'

import type { ChartConfig } from '@/components/ui/chart'

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  ChartContainer,
  ChartCrosshair,
  ChartLegendContent,
  ChartTooltip,
  ChartTooltipContent,
  componentToString,
} from '@/components/ui/chart'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const analyticsChartData = [
  { date: new Date('2024-04-01'), pageViews: 1520, uniqueVisitors: 980 },
  { date: new Date('2024-04-02'), pageViews: 1890, uniqueVisitors: 1200 },
  { date: new Date('2024-04-03'), pageViews: 2100, uniqueVisitors: 1350 },
  { date: new Date('2024-04-04'), pageViews: 1750, uniqueVisitors: 1100 },
  { date: new Date('2024-04-05'), pageViews: 2300, uniqueVisitors: 1450 },
  { date: new Date('2024-04-06'), pageViews: 1980, uniqueVisitors: 1250 },
  { date: new Date('2024-04-07'), pageViews: 1650, uniqueVisitors: 1050 },
  { date: new Date('2024-04-08'), pageViews: 2450, uniqueVisitors: 1580 },
  { date: new Date('2024-04-09'), pageViews: 1420, uniqueVisitors: 920 },
  { date: new Date('2024-04-10'), pageViews: 2180, uniqueVisitors: 1380 },
  { date: new Date('2024-04-11'), pageViews: 2650, uniqueVisitors: 1680 },
  { date: new Date('2024-04-12'), pageViews: 1920, uniqueVisitors: 1220 },
  { date: new Date('2024-04-13'), pageViews: 2780, uniqueVisitors: 1750 },
  { date: new Date('2024-04-14'), pageViews: 1580, uniqueVisitors: 1000 },
  { date: new Date('2024-04-15'), pageViews: 1350, uniqueVisitors: 880 },
  { date: new Date('2024-04-16'), pageViews: 1480, uniqueVisitors: 950 },
  { date: new Date('2024-04-17'), pageViews: 3120, uniqueVisitors: 1980 },
  { date: new Date('2024-04-18'), pageViews: 2890, uniqueVisitors: 1820 },
  { date: new Date('2024-04-19'), pageViews: 2050, uniqueVisitors: 1300 },
  { date: new Date('2024-04-20'), pageViews: 1620, uniqueVisitors: 1020 },
  { date: new Date('2024-04-21'), pageViews: 1780, uniqueVisitors: 1120 },
  { date: new Date('2024-04-22'), pageViews: 2240, uniqueVisitors: 1420 },
  { date: new Date('2024-04-23'), pageViews: 1680, uniqueVisitors: 1080 },
  { date: new Date('2024-04-24'), pageViews: 2980, uniqueVisitors: 1880 },
  { date: new Date('2024-04-25'), pageViews: 2150, uniqueVisitors: 1360 },
  { date: new Date('2024-04-26'), pageViews: 1520, uniqueVisitors: 980 },
  { date: new Date('2024-04-27'), pageViews: 3280, uniqueVisitors: 2080 },
  { date: new Date('2024-04-28'), pageViews: 1820, uniqueVisitors: 1150 },
  { date: new Date('2024-04-29'), pageViews: 2650, uniqueVisitors: 1680 },
  { date: new Date('2024-04-30'), pageViews: 3420, uniqueVisitors: 2160 },
  { date: new Date('2024-05-01'), pageViews: 1980, uniqueVisitors: 1250 },
  { date: new Date('2024-05-02'), pageViews: 2850, uniqueVisitors: 1800 },
  { date: new Date('2024-05-03'), pageViews: 2280, uniqueVisitors: 1440 },
  { date: new Date('2024-05-04'), pageViews: 3120, uniqueVisitors: 1980 },
  { date: new Date('2024-05-05'), pageViews: 3850, uniqueVisitors: 2430 },
  { date: new Date('2024-05-06'), pageViews: 4120, uniqueVisitors: 2600 },
  { date: new Date('2024-05-07'), pageViews: 3280, uniqueVisitors: 2080 },
  { date: new Date('2024-05-08'), pageViews: 1980, uniqueVisitors: 1250 },
  { date: new Date('2024-05-09'), pageViews: 2450, uniqueVisitors: 1550 },
  { date: new Date('2024-05-10'), pageViews: 2980, uniqueVisitors: 1880 },
  { date: new Date('2024-05-11'), pageViews: 3120, uniqueVisitors: 1980 },
  { date: new Date('2024-05-12'), pageViews: 2180, uniqueVisitors: 1380 },
  { date: new Date('2024-05-13'), pageViews: 1980, uniqueVisitors: 1250 },
  { date: new Date('2024-05-14'), pageViews: 3650, uniqueVisitors: 2310 },
  { date: new Date('2024-05-15'), pageViews: 3820, uniqueVisitors: 2420 },
  { date: new Date('2024-05-16'), pageViews: 2980, uniqueVisitors: 1880 },
  { date: new Date('2024-05-17'), pageViews: 4120, uniqueVisitors: 2600 },
  { date: new Date('2024-05-18'), pageViews: 3120, uniqueVisitors: 1980 },
  { date: new Date('2024-05-19'), pageViews: 2450, uniqueVisitors: 1550 },
  { date: new Date('2024-05-20'), pageViews: 1980, uniqueVisitors: 1250 },
  { date: new Date('2024-05-21'), pageViews: 1520, uniqueVisitors: 980 },
  { date: new Date('2024-05-22'), pageViews: 1480, uniqueVisitors: 950 },
  { date: new Date('2024-05-23'), pageViews: 2650, uniqueVisitors: 1680 },
  { date: new Date('2024-05-24'), pageViews: 2980, uniqueVisitors: 1880 },
  { date: new Date('2024-05-25'), pageViews: 2180, uniqueVisitors: 1380 },
  { date: new Date('2024-05-26'), pageViews: 2250, uniqueVisitors: 1420 },
  { date: new Date('2024-05-27'), pageViews: 3650, uniqueVisitors: 2310 },
  { date: new Date('2024-05-28'), pageViews: 2450, uniqueVisitors: 1550 },
  { date: new Date('2024-05-29'), pageViews: 1520, uniqueVisitors: 980 },
  { date: new Date('2024-05-30'), pageViews: 3120, uniqueVisitors: 1980 },
  { date: new Date('2024-05-31'), pageViews: 1980, uniqueVisitors: 1250 },
]

type AnalyticsData = (typeof analyticsChartData)[number]

const analyticsChartConfig = {
  pageViews: {
    label: 'Page Views',
    color: 'var(--chart-1)',
  },
  uniqueVisitors: {
    label: 'Unique Visitors',
    color: 'var(--chart-2)',
  },
} satisfies ChartConfig

const svgDefs = `
  <linearGradient id="fillPageViews" x1="0" y1="0" x2="0" y2="1">
    <stop
      offset="5%"
      stop-color="var(--color-pageViews)"
      stop-opacity="0.8"
    />
    <stop
      offset="95%"
      stop-color="var(--color-pageViews)"
      stop-opacity="0.1"
    />
  </linearGradient>
  <linearGradient id="fillUniqueVisitors" x1="0" y1="0" x2="0" y2="1">
    <stop
      offset="5%"
      stop-color="var(--color-uniqueVisitors)"
      stop-opacity="0.8"
    />
    <stop
      offset="95%"
      stop-color="var(--color-uniqueVisitors)"
      stop-opacity="0.1"
    />
  </linearGradient>
`

const timeRange = ref('90d')
const filterRange = computed(() => {
  return analyticsChartData.filter((item) => {
    const date = new Date(item.date)
    const referenceDate = new Date('2024-05-31')
    let daysToSubtract = 90
    if (timeRange.value === '30d') {
      daysToSubtract = 30
    } else if (timeRange.value === '7d') {
      daysToSubtract = 7
    }
    const startDate = new Date(referenceDate)
    startDate.setDate(startDate.getDate() - daysToSubtract)
    return date >= startDate
  })
})
</script>

<template>
  <div
    class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
    data-testid="analytics-content_metrics_grid"
  >
    <UiCard data-testid="analytics-content_page-views_card">
      <UiCardHeader
        class="flex flex-row items-center justify-between pb-2 space-y-0"
      >
        <UiCardTitle class="text-sm font-medium"> Page Views </UiCardTitle>
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
          <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
          <circle cx="12" cy="12" r="3" />
        </svg>
      </UiCardHeader>
      <UiCardContent>
        <div class="text-2xl font-bold">124,580</div>
        <p class="text-xs text-muted-foreground">+15.3% from last month</p>
      </UiCardContent>
    </UiCard>
    <UiCard data-testid="analytics-content_unique-visitors_card">
      <UiCardHeader
        class="flex flex-row items-center justify-between pb-2 space-y-0"
      >
        <UiCardTitle class="text-sm font-medium"> Unique Visitors </UiCardTitle>
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
        <div class="text-2xl font-bold">78,420</div>
        <p class="text-xs text-muted-foreground">+12.8% from last month</p>
      </UiCardContent>
    </UiCard>
    <UiCard data-testid="analytics-content_bounce-rate_card">
      <UiCardHeader
        class="flex flex-row items-center justify-between pb-2 space-y-0"
      >
        <UiCardTitle class="text-sm font-medium"> Bounce Rate </UiCardTitle>
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
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
          <polyline points="9 22 9 12 15 12 15 22" />
        </svg>
      </UiCardHeader>
      <UiCardContent>
        <div class="text-2xl font-bold">42.5%</div>
        <p class="text-xs text-muted-foreground">-3.2% from last month</p>
      </UiCardContent>
    </UiCard>
    <UiCard data-testid="analytics-content_avg-session_card">
      <UiCardHeader
        class="flex flex-row items-center justify-between pb-2 space-y-0"
      >
        <UiCardTitle class="text-sm font-medium">
          Avg Session Duration
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
        <div class="text-2xl font-bold">3m 24s</div>
        <p class="text-xs text-muted-foreground">+8.5% from last month</p>
      </UiCardContent>
    </UiCard>
  </div>

  <div class="grid grid-cols-1 gap-4 lg:grid-cols-7">
    <Card
      class="col-span-1 lg:col-span-7 pt-0"
      data-testid="analytics-content_chart_card"
    >
      <CardHeader
        class="flex items-center gap-2 space-y-0 border-b py-5 sm:flex-row"
      >
        <div class="grid flex-1 gap-1">
          <CardTitle>Analytics Overview</CardTitle>
          <CardDescription>
            Showing page views and unique visitors for the last 3 months
          </CardDescription>
        </div>
        <Select
          v-model="timeRange"
          data-testid="analytics-content_time-range_select"
        >
          <SelectTrigger
            class="hidden w-[160px] rounded-lg sm:ml-auto sm:flex"
            aria-label="Select a value"
          >
            <SelectValue placeholder="Last 3 months" />
          </SelectTrigger>
          <SelectContent class="rounded-xl">
            <SelectItem value="90d" class="rounded-lg">
              Last 3 months
            </SelectItem>
            <SelectItem value="30d" class="rounded-lg">
              Last 30 days
            </SelectItem>
            <SelectItem value="7d" class="rounded-lg"> Last 7 days </SelectItem>
          </SelectContent>
        </Select>
      </CardHeader>
      <CardContent class="px-2 pt-4 sm:px-6 sm:pt-6 pb-4">
        <ChartContainer
          :config="analyticsChartConfig"
          class="aspect-auto h-[250px] w-full"
          :cursor="false"
        >
          <VisXYContainer
            :data="filterRange"
            :svg-defs="svgDefs"
            :margin="{ left: -40 }"
            :y-domain="[0, 4500]"
          >
            <VisArea
              :x="(d: AnalyticsData) => d.date"
              :y="[
                (d: AnalyticsData) => d.uniqueVisitors,
                (d: AnalyticsData) => d.pageViews,
              ]"
              :color="
                (_d: AnalyticsData, i: number) =>
                  ['url(#fillUniqueVisitors)', 'url(#fillPageViews)'][i]
              "
              :opacity="0.6"
            />
            <VisLine
              :x="(d: AnalyticsData) => d.date"
              :y="[
                (d: AnalyticsData) => d.uniqueVisitors,
                (d: AnalyticsData) => d.pageViews,
              ]"
              :color="
                (_d: AnalyticsData, i: number) =>
                  [
                    analyticsChartConfig.uniqueVisitors.color,
                    analyticsChartConfig.pageViews.color,
                  ][i]
              "
              :line-width="1"
            />
            <VisAxis
              type="x"
              :x="(d: AnalyticsData) => d.date"
              :tick-line="false"
              :domain-line="false"
              :grid-line="false"
              :num-ticks="6"
              :tick-format="
                (d: number, _index: number) => {
                  const date = new Date(d)
                  return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                  })
                }
              "
            />
            <VisAxis
              type="y"
              :num-ticks="3"
              :tick-line="false"
              :domain-line="false"
            />
            <ChartTooltip />
            <ChartCrosshair
              :template="
                componentToString(analyticsChartConfig, ChartTooltipContent, {
                  labelFormatter: (d: number) => {
                    return new Date(d).toLocaleDateString('en-US', {
                      month: 'short',
                      day: 'numeric',
                    })
                  },
                })
              "
              :color="
                (_d: AnalyticsData, i: number) =>
                  [
                    analyticsChartConfig.uniqueVisitors.color,
                    analyticsChartConfig.pageViews.color,
                  ][i % 2]
              "
            />
          </VisXYContainer>

          <ChartLegendContent />
        </ChartContainer>
      </CardContent>
    </Card>
  </div>
</template>
