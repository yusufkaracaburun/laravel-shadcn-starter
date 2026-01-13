<script setup lang="ts">
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import Badge from '@/components/ui/badge/Badge.vue'

// Mock data - in real app, this would come from API
const skills = [
  'Photoshop',
  'Figma',
  'HTML',
  'React',
  'Tailwind CSS',
  'CSS',
  'Laravel',
  'Node.js',
]
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Skills</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="flex flex-wrap gap-2">
        <Badge
          v-for="skill in skills"
          :key="skill"
          variant="secondary"
          class="text-sm"
        >
          {{ skill }}
        </Badge>
      </div>
    </CardContent>
  </Card>
</template>
