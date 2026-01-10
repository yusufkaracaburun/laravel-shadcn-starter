<script setup lang="ts">
import type { IUser } from '@/pages/users/models/users'

import Badge from '@/components/ui/badge/Badge.vue'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { UsersIcon } from '@/composables/use-icons.composable'

interface Props {
  user: IUser
}

defineProps<Props>()
</script>

<template>
  <Card v-if="user.teams?.length">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <UsersIcon class="size-5" />
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
            v-if="user.current_team_id === team.id"
            variant="default"
          >
            Current
          </Badge>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
