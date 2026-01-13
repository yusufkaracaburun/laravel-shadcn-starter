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
import { ShieldIcon } from '@/composables/use-icons.composable'

interface Props {
  user: IUser
}

defineProps<Props>()
</script>

<template>
  <Card v-if="user.roles?.length">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <ShieldIcon class="size-5" />
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
</template>
