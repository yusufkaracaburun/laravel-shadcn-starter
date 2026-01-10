<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, ref } from 'vue'

import type { ICompany } from '@/pages/companies/models/companies'

import Page from '@/components/global-layout/basic-page.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { useCompanies } from '@/composables/use-companies.composable'
import {
  FileTextIcon,
  LayoutGridIcon,
  UserCircleIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'

import CompanyAccountStatusCard from './components/company-account-status-card.vue'
import CompanyEmployeesCard from './components/company-employees-card.vue'
import CompanyHeader from './components/company-header.vue'
import CompanyInvoicesCard from './components/company-invoices-card.vue'
import CompanyNavbar from './components/company-navbar.vue'
import CompanyProfileCard from './components/company-profile-card.vue'
import CompanyViewLayout from './components/company-view-layout.vue'

// Composables
const {
  companyByIdResponse,
  isLoadingCompanyById,
  isErrorCompanyById,
  errorCompanyById,
  fetchCompanyByIdData,
} = useCompanies()

// Computed properties
const company = computed<ICompany | null>(() => {
  const data = companyByIdResponse.value?.data
  // Handle case where data might be an array (shouldn't happen, but type safety)
  if (Array.isArray(data)) {
    return data[0] ?? null
  }
  return data ?? null
})

const pageTitle = computed(() => company.value?.name ?? 'Company Details')

const pageDescription = computed(() =>
  company.value
    ? `View details for ${company.value.email}`
    : 'Loading company information...',
)

// Computed values for components
const companyInitials = computed(() => {
  if (!company.value) {
    return '?'
  }

  const name = company.value.name
  if (!name || name === '—') {
    return '?'
  }

  const parts = name.trim().split(/\s+/)

  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }

  return name[0].toUpperCase()
})

const formattedCreatedAt = computed(() => {
  if (!company.value?.created_at) {
    return '—'
  }

  return new Date(company.value.created_at).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

const formattedUpdatedAt = computed(() => {
  if (!company.value?.updated_at) {
    return '—'
  }

  return new Date(company.value.updated_at).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

// Tab state
const activeTab = ref('overview')

// Event handlers
function handleEditClosed() {
  fetchCompanyByIdData()
}

function handleDeleteClosed() {
  // Company will be redirected by the navbar component
}
</script>

<template>
  <Page :title="pageTitle" :description="pageDescription">
    <template #actions>
      <CompanyNavbar
        v-if="company"
        :company="company"
        @edit-closed="handleEditClosed"
        @delete-closed="handleDeleteClosed"
      />
    </template>

    <CompanyViewLayout
      :is-loading="isLoadingCompanyById"
      :is-error="isErrorCompanyById"
      :error-object="errorCompanyById"
      :on-retry="fetchCompanyByIdData"
    >
      <template v-if="company">
        <div class="space-y-8">
          <!-- Enhanced Header Section -->
          <div
            class="relative overflow-hidden rounded-xl border bg-gradient-to-br from-background to-muted/20 p-8 shadow-sm"
          >
            <div class="relative z-10">
              <CompanyHeader :company="company" :initials="companyInitials" />
            </div>
          </div>

          <!-- Modern Tabs Section -->
          <div class="space-y-6">
            <Tabs v-model="activeTab" class="w-full">
              <TabsList
                class="h-auto w-full justify-start gap-1 bg-muted/50 p-1"
              >
                <TabsTrigger
                  value="overview"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <LayoutGridIcon class="size-4" />
                  <span>Overview</span>
                </TabsTrigger>
                <TabsTrigger
                  value="account"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <UserCircleIcon class="size-4" />
                  <span>Account</span>
                </TabsTrigger>
                <TabsTrigger
                  value="employees"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <UsersIcon class="size-4" />
                  <span>Employees</span>
                </TabsTrigger>
                <TabsTrigger
                  value="invoices"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <FileTextIcon class="size-4" />
                  <span>Invoices</span>
                </TabsTrigger>
              </TabsList>

              <TabsContent value="overview" class="mt-8">
                <div class="grid gap-6 lg:grid-cols-2">
                  <CompanyProfileCard :company="company" />

                  <CompanyAccountStatusCard
                    :company="company"
                    :created-at="formattedCreatedAt"
                    :updated-at="formattedUpdatedAt"
                  />
                </div>
              </TabsContent>

              <TabsContent value="account" class="mt-8">
                <div class="max-w-2xl">
                  <CompanyAccountStatusCard
                    :company="company"
                    :created-at="formattedCreatedAt"
                    :updated-at="formattedUpdatedAt"
                  />
                </div>
              </TabsContent>

              <TabsContent value="employees" class="mt-8">
                <div class="max-w-2xl">
                  <CompanyEmployeesCard :company="company" />
                </div>
              </TabsContent>

              <TabsContent value="invoices" class="mt-8">
                <div class="max-w-2xl">
                  <CompanyInvoicesCard :company="company" />
                </div>
              </TabsContent>
            </Tabs>
          </div>
        </div>
      </template>
    </CompanyViewLayout>
  </Page>
</template>
