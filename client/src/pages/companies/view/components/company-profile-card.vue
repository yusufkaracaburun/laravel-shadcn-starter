<script setup lang="ts">
import type { ICompany } from '@/pages/companies/models/companies'

import Badge from '@/components/ui/badge/Badge.vue'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { MailIcon, PhoneIcon, UsersIcon } from '@/composables/use-icons.composable'
import { StatusBadge } from '@/components/ui/status-badge'

import { employeeSizes, industries, statuses } from '../../data/data'

interface Props {
  company: ICompany
}

const props = defineProps<Props>()

const industryLabel = computed(() => {
  return industries.find(i => i.value === props.company.industry)?.label || props.company.industry
})

const employeeSizeLabel = computed(() => {
  return employeeSizes.find(e => e.value === props.company.employees)?.label || props.company.employees
})

const statusInfo = computed(() => {
  return statuses.find(s => s.value === props.company.status) || null
})
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Company Information</CardTitle>
      <CardDescription>Basic company details</CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Name
        </div>
        <div class="text-base">
          {{ company.name }}
        </div>
      </div>
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Email
        </div>
        <div class="text-base">
          {{ company.email }}
        </div>
      </div>
      <div v-if="company.phone">
        <div
          class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
        >
          <PhoneIcon class="size-4" />
          Phone
        </div>
        <div class="text-base">
          {{ company.phone }}
        </div>
      </div>
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Industry
        </div>
        <div class="text-base">
          <Badge variant="outline">
            {{ industryLabel }}
          </Badge>
        </div>
      </div>
      <div>
        <div class="text-sm font-medium text-muted-foreground mb-1">
          Status
        </div>
        <div class="text-base">
          <StatusBadge
            v-if="statusInfo"
            :status="company.status"
            type="company"
            :icon="statusInfo.icon"
            :label="statusInfo.label"
          />
        </div>
      </div>
      <div>
        <div
          class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2"
        >
          <UsersIcon class="size-4" />
          Employees
        </div>
        <div class="text-base">
          {{ employeeSizeLabel }}
        </div>
      </div>
    </CardContent>
  </Card>
</template>
