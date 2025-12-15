<script lang="ts" setup>
import { Button } from '@/components/ui/button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

interface Report {
  id: string
  name: string
  dateGenerated: string
  status: 'completed' | 'processing' | 'failed'
  type: string
}

const reports = ref<Report[]>([
  {
    id: '1',
    name: 'Monthly Sales Report',
    dateGenerated: '2024-06-15',
    status: 'completed',
    type: 'Sales',
  },
  {
    id: '2',
    name: 'User Activity Report',
    dateGenerated: '2024-06-14',
    status: 'completed',
    type: 'Analytics',
  },
  {
    id: '3',
    name: 'Revenue Summary Q2',
    dateGenerated: '2024-06-13',
    status: 'processing',
    type: 'Financial',
  },
  {
    id: '4',
    name: 'Customer Retention Report',
    dateGenerated: '2024-06-12',
    status: 'completed',
    type: 'Customer',
  },
  {
    id: '5',
    name: 'Product Performance Report',
    dateGenerated: '2024-06-11',
    status: 'failed',
    type: 'Product',
  },
  {
    id: '6',
    name: 'Marketing Campaign Report',
    dateGenerated: '2024-06-10',
    status: 'completed',
    type: 'Marketing',
  },
])

const getStatusColor = (status: Report['status']) => {
  switch (status) {
    case 'completed':
      return 'text-green-600'
    case 'processing':
      return 'text-yellow-600'
    case 'failed':
      return 'text-red-600'
    default:
      return 'text-muted-foreground'
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <UiCard data-testid="reports-content_card">
    <UiCardHeader>
      <UiCardTitle>Reports</UiCardTitle>
      <UiCardDescription> View and download your generated reports </UiCardDescription>
    </UiCardHeader>
    <UiCardContent>
      <div class="border rounded-md" data-testid="reports-content_table">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Report Name</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Date Generated</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="report in reports"
              :key="report.id"
              data-testid="reports-content_report-row"
            >
              <TableCell class="font-medium">{{ report.name }}</TableCell>
              <TableCell>{{ report.type }}</TableCell>
              <TableCell>{{ formatDate(report.dateGenerated) }}</TableCell>
              <TableCell>
                <span :class="getStatusColor(report.status)" class="capitalize">
                  {{ report.status }}
                </span>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button
                    v-if="report.status === 'completed'"
                    variant="outline"
                    size="sm"
                    data-testid="reports-content_view-button"
                  >
                    View
                  </Button>
                  <Button
                    v-if="report.status === 'completed'"
                    variant="outline"
                    size="sm"
                    data-testid="reports-content_download-button"
                  >
                    Download
                  </Button>
                  <Button
                    v-if="report.status === 'failed'"
                    variant="outline"
                    size="sm"
                    data-testid="reports-content_retry-button"
                  >
                    Retry
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </UiCardContent>
  </UiCard>
</template>
