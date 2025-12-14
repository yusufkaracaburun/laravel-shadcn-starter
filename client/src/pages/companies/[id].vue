<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { ArrowLeft, Building2, Calendar, FilePenLine, Mail, Phone, Trash2 } from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import type { Company } from '@/services/companies.service'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { useGetCompanyQuery } from '@/services/companies.service'

import CompanyDelete from './components/company-delete.vue'
import CompanyResourceDialog from './components/company-resource-dialog.vue'

const route = useRoute()
const router = useRouter()

const companyId = computed(() => Number(route.params.id))

const { data: companyResponse, isLoading, isError, error, refetch } = useGetCompanyQuery(companyId)

const company = computed<Company | null>(() => companyResponse.value?.data ?? null)

// Format date
function formatDate(dateString: string | null): string {
  if (!dateString)
    return '—'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

// Format datetime
function formatDateTime(dateString: string | null): string {
  if (!dateString)
    return '—'
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Handle edit
const editDialogOpen = ref(false)

function handleEditClose() {
  editDialogOpen.value = false
  // Refetch company data after edit
  refetch()
}

// Handle delete
const deleteDialogOpen = ref(false)

function handleDeleteClose() {
  deleteDialogOpen.value = false
  // Navigate back to companies list after deletion
  router.push('/companies')
}

// Check if error is 404
const isNotFound = computed(() => {
  if (!isError.value || !error.value)
    return false
  return (error.value as any)?.response?.status === 404
})

// Get status badge variant
function getStatusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
  switch (status) {
    case 'active':
      return 'default'
    case 'inactive':
      return 'secondary'
    case 'pending':
      return 'outline'
    default:
      return 'secondary'
  }
}

// Get industry label
function getIndustryLabel(industry: string): string {
  const industries: Record<string, string> = {
    technology: 'Technology',
    finance: 'Finance',
    healthcare: 'Healthcare',
    retail: 'Retail',
    manufacturing: 'Manufacturing',
    education: 'Education',
  }
  return industries[industry] || industry
}
</script>

<template>
  <Page
    :title="company ? company.name : 'Company Details'"
    :description="company ? `View details for ${company.email}` : 'Loading company information...'"
  >
    <template #actions>
      <div v-if="company" class="flex items-center gap-2">
        <Button variant="outline" @click="router.push('/companies')">
          <ArrowLeft class="mr-2 size-4" />
          Back
        </Button>
        <Button variant="outline" @click="editDialogOpen = true">
          <FilePenLine class="mr-2 size-4" />
          Edit
        </Button>
        <Button variant="destructive" @click="deleteDialogOpen = true">
          <Trash2 class="mr-2 size-4" />
          Delete
        </Button>
      </div>
    </template>

    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <Loading />
    </div>

    <div v-else-if="isError && isNotFound" class="flex items-center justify-center min-h-[400px]">
      <Error
        :code="404"
        subtitle="Company Not Found"
        error="The company you are looking for might have been removed or doesn't exist."
      />
    </div>

    <div v-else-if="isError" class="flex items-center justify-center min-h-[400px]">
      <div class="text-center">
        <Error
          :code="500"
          subtitle="Error Loading Company"
          error="An error occurred while loading the company information. Please try again."
        />
        <Button class="mt-4" @click="refetch()">
          Retry
        </Button>
      </div>
    </div>

    <div v-else-if="company" class="space-y-6">
      <!-- Header Section -->
      <Card>
        <CardHeader>
          <div class="flex items-start gap-6">
            <div class="flex size-24 items-center justify-center rounded-full bg-primary/10">
              <Building2 class="size-12 text-primary" />
            </div>
            <div class="flex-1">
              <CardTitle class="text-3xl mb-2">
                {{ company.name }}
              </CardTitle>
              <CardDescription class="text-base flex items-center gap-2">
                <Mail class="size-4" />
                {{ company.email }}
              </CardDescription>
              <div class="mt-4 flex items-center gap-2">
                <Badge :variant="getStatusVariant(company.status)">
                  {{ company.status.charAt(0).toUpperCase() + company.status.slice(1) }}
                </Badge>
              </div>
            </div>
          </div>
        </CardHeader>
      </Card>

      <!-- Information Grid -->
      <div class="grid gap-6 md:grid-cols-2">
        <!-- Company Information -->
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
              <div class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2">
                <Mail class="size-4" />
                Email
              </div>
              <div class="text-base">
                {{ company.email }}
              </div>
            </div>
            <div v-if="company.phone">
              <div class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2">
                <Phone class="size-4" />
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
                  {{ getIndustryLabel(company.industry) }}
                </Badge>
              </div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Employees
              </div>
              <div class="text-base">
                {{ company.employees }}
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Status & Metadata -->
        <Card>
          <CardHeader>
            <CardTitle>Status & Metadata</CardTitle>
            <CardDescription>Company status and account information</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Status
              </div>
              <div class="text-base">
                <Badge :variant="getStatusVariant(company.status)">
                  {{ company.status.charAt(0).toUpperCase() + company.status.slice(1) }}
                </Badge>
              </div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2">
                <Calendar class="size-4" />
                Created At
              </div>
              <div class="text-base">
                {{ formatDateTime(company.created_at) }}
              </div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1 flex items-center gap-2">
                <Calendar class="size-4" />
                Updated At
              </div>
              <div class="text-base">
                {{ formatDateTime(company.updated_at) }}
              </div>
            </div>
            <div v-if="company.team_id">
              <div class="text-sm font-medium text-muted-foreground mb-1">
                Team ID
              </div>
              <div class="text-base">
                {{ company.team_id }}
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Edit Dialog -->
    <UiDialog v-if="company" v-model:open="editDialogOpen">
      <UiDialogContent>
        <CompanyResourceDialog :company="company" @close="handleEditClose" />
      </UiDialogContent>
    </UiDialog>

    <!-- Delete Dialog -->
    <UiDialog v-if="company" v-model:open="deleteDialogOpen">
      <UiDialogContent>
        <CompanyDelete :company="company" @close="handleDeleteClose" />
      </UiDialogContent>
    </UiDialog>
  </Page>
</template>
