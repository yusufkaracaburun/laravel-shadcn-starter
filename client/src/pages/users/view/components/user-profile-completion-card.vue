<script setup lang="ts">
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'

// Mock data - in real app, this would come from API
const profileCompletion = 66
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Complete Your Profile</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="flex items-center justify-between gap-4">
        <Progress :model-value="profileCompletion" class="flex-1" />
        <span class="text-sm font-medium text-muted-foreground">
          %{{ profileCompletion }}
        </span>
      </div>
    </CardContent>
  </Card>
</template>
