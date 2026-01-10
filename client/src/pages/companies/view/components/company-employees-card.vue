<script setup lang="ts">
import type { ICompany } from '@/pages/companies/models/companies'

import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import Loading from '@/components/loading.vue'
import { UsersIcon } from '@/composables/use-icons.composable'

interface Props {
  company: ICompany
}

const props = defineProps<Props>()

// TODO: Fetch employees for this company
// This is a placeholder component - implement actual employee fetching logic
const loading = ref(false)
const employees = ref([])
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        <UsersIcon class="size-5" />
        Employees
      </CardTitle>
      <CardDescription>Employees associated with this company</CardDescription>
    </CardHeader>
    <CardContent>
      <div v-if="loading" class="flex items-center justify-center py-8">
        <Loading />
      </div>
      <div
        v-else-if="!employees || employees.length === 0"
        class="flex items-center justify-center py-8"
      >
        <p class="text-sm text-muted-foreground">
          No employees found.
        </p>
      </div>
      <div v-else class="space-y-3">
        <!-- TODO: Implement employee list display -->
        <p class="text-sm text-muted-foreground">
          Employee list will be displayed here.
        </p>
      </div>
    </CardContent>
  </Card>
</template>
