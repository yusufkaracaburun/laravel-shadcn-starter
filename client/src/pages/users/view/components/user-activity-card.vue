<script setup lang="ts">
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { FileTextIcon, DownloadIcon } from '@/composables/use-icons.composable'
import Badge from '@/components/ui/badge/Badge.vue'

// Mock data - in real app, this would come from API
const activities = [
  {
    id: 1,
    title: 'Shadcn UI Kit Application UI v2.0.0',
    date: 'Released on December 2nd, 2025',
    description: 'Get access to over 20+ pages including a dashboard layout, charts, kanban board, calendar, and pre-order E-commerce & Marketing pages.',
    isLatest: true,
    hasDownload: true,
  },
  {
    id: 2,
    title: 'Shadcn UI Kit Figma v1.3.0',
    date: 'Released on December 2nd, 2025',
    description: 'All of the pages and components are first designed in Figma and we keep a parity between the two versions even as we update the project.',
    isLatest: false,
    hasDownload: false,
  },
  {
    id: 3,
    title: 'Shadcn UI Kit Library v1.2.2',
    date: 'Released on December 2nd, 2025',
    description: 'Get started with dozens of web components and interactive elements built on top of Tailwind CSS.',
    isLatest: false,
    hasDownload: false,
  },
]
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex items-center justify-between">
        <CardTitle>Latest Activity</CardTitle>
        <a
          href="#"
          class="text-sm text-primary hover:underline"
        >
          View All
        </a>
      </div>
    </CardHeader>
    <CardContent class="space-y-6">
      <div
        v-for="activity in activities"
        :key="activity.id"
        class="space-y-2"
      >
        <div class="flex items-start gap-3">
          <div class="mt-1">
            <FileTextIcon class="size-5 text-muted-foreground" />
          </div>
          <div class="flex-1 space-y-1">
            <div class="flex items-center gap-2">
              <h4 class="font-medium">
                {{ activity.title }}
              </h4>
              <Badge
                v-if="activity.isLatest"
                variant="default"
                class="bg-green-500 text-white text-xs"
              >
                Latest
              </Badge>
            </div>
            <p class="text-sm text-muted-foreground">
              {{ activity.date }}
            </p>
            <p class="text-sm text-muted-foreground">
              {{ activity.description }}
            </p>
            <Button
              v-if="activity.hasDownload"
              variant="outline"
              size="sm"
              class="mt-2"
            >
              <DownloadIcon class="mr-2 size-4" />
              Download ZIP
            </Button>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
