<script setup lang="ts">
import type { IUser } from '@/pages/users/models/users'

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { CalendarIcon } from '@/composables/use-icons.composable'

interface Props {
  user: IUser
  createdAt: string
  updatedAt: string
}

defineProps<Props>()
</script>

<template>
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
          <CalendarIcon class="size-4" />
          Created At
        </div>
        <div class="text-base">
          {{ createdAt }}
        </div>
      </div>
      <div>
        <div
          class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
        >
          <CalendarIcon class="size-4" />
          Updated At
        </div>
        <div class="text-base">
          {{ updatedAt }}
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
</template>
