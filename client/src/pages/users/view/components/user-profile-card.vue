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

interface Props {
  user: IUser
  isEmailVerified: boolean
  emailVerifiedAt: string | null
}

defineProps<Props>()
</script>

<template>
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
          <Badge :variant="isEmailVerified ? 'default' : 'secondary'">
            {{ isEmailVerified ? 'Verified' : 'Unverified' }}
          </Badge>
          <span
            v-if="isEmailVerified && emailVerifiedAt"
            class="ml-2 text-sm text-muted-foreground"
          >
            {{ emailVerifiedAt }}
          </span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
