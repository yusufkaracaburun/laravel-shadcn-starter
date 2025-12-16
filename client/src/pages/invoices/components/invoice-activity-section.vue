<script setup lang="ts">
import { History } from 'lucide-vue-next'

import type { IInvoiceActivity } from '@/services/invoices.service'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'

import { formatDateTime } from '../utils/formatters'

interface IProps {
  activities: IInvoiceActivity[]
}

const props = defineProps<IProps>()

function getActivityDescription(activity: IInvoiceActivity): string {
  return activity.description || 'Activity recorded'
}

function hasChanges(activity: IInvoiceActivity): boolean {
  const oldValues = activity.properties?.old || {}
  const newValues = activity.properties?.attributes || {}
  return Object.keys(oldValues).length > 0 || Object.keys(newValues).length > 0
}
</script>

<template>
  <Card>
    <CardHeader class="pb-4">
      <div class="flex items-center gap-2">
        <History class="size-4 text-muted-foreground" />
        <CardTitle class="text-base font-semibold"> Activity Log </CardTitle>
      </div>
    </CardHeader>
    <CardContent>
      <div v-if="props.activities.length > 0" class="space-y-4">
        <div v-for="(activity, index) in props.activities" :key="activity.id" class="relative pl-6">
          <div class="absolute left-0 top-1.5 size-2 rounded-full bg-primary" />
          <div class="space-y-1.5">
            <div class="flex items-start justify-between gap-2">
              <div class="flex-1">
                <p class="text-sm font-medium text-foreground">
                  {{ getActivityDescription(activity) }}
                </p>
                <div class="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                  <span v-if="activity.causer">
                    {{ activity.causer.name }}
                  </span>
                  <span v-else> System </span>
                  <span>•</span>
                  <span>{{ formatDateTime(activity.created_at) }}</span>
                </div>
              </div>
            </div>
            <div v-if="hasChanges(activity)" class="mt-2 rounded-md bg-muted/50 p-2 text-xs">
              <div
                v-for="(value, key) in activity.properties.attributes"
                :key="key"
                class="flex items-start gap-2"
              >
                <span class="font-medium text-muted-foreground">{{ key }}:</span>
                <span class="text-foreground">
                  {{
                    activity.properties.old[key] !== undefined
                      ? `${activity.properties.old[key]} → ${value}`
                      : value
                  }}
                </span>
              </div>
            </div>
          </div>
          <Separator v-if="index < props.activities.length - 1" class="mt-4" />
        </div>
      </div>
      <div v-else class="py-8 text-center">
        <div class="inline-flex items-center justify-center size-10 rounded-full bg-muted mb-3">
          <History class="size-5 text-muted-foreground" />
        </div>
        <p class="text-sm font-medium text-muted-foreground">No activity recorded</p>
        <p class="text-xs text-muted-foreground mt-1">Activity history will appear here.</p>
      </div>
    </CardContent>
  </Card>
</template>
