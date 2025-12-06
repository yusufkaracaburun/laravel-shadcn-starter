<script lang="ts" setup>
import { toast } from 'vue-sonner'

import Page from '@/components/global-layout/basic-page.vue'
import { Button } from '@/components/ui/button'

import AnalyticsContent from './components/analytics-content.vue'
import NotificationsContent from './components/notifications-content.vue'
import OverviewContent from './components/overview-content.vue'
import ProjectsContent from './components/projects-content.vue'
import ReportsContent from './components/reports-content.vue'

const tabs = ref([
  { name: 'Overview', value: 'overview' },
  { name: 'Analytics', value: 'analytics' },
  { name: 'Projects', value: 'projects' },
  { name: 'Reports', value: 'reports' },
  { name: 'Notifications', value: 'notifications' },
])

const activeTab = ref(tabs.value[0].value)
</script>

<template>
  <Page title="workspace" description="workspace description" sticky data-testid="dashboard_page">
    <template #actions>
      <Button
        @click="
          () =>
            toast('hello', {
              position: 'top-center',
            })
        "
      >
        Download
      </Button>
    </template>

    <UiTabs :default-value="activeTab" class="w-full">
      <UiTabsList>
        <UiTabsTrigger
          v-for="tab in tabs"
          :key="tab.value"
          :value="tab.value"
        >
          {{ tab.name }}
        </UiTabsTrigger>
      </UiTabsList>
      <UiTabsContent value="overview" class="space-y-4">
        <OverviewContent />
      </UiTabsContent>
      <UiTabsContent value="analytics" class="space-y-4">
        <AnalyticsContent />
      </UiTabsContent>
      <UiTabsContent value="projects" class="space-y-4">
        <ProjectsContent />
      </UiTabsContent>
      <UiTabsContent value="reports" class="space-y-4">
        <ReportsContent />
      </UiTabsContent>
      <UiTabsContent value="notifications" class="space-y-4">
        <NotificationsContent />
      </UiTabsContent>
    </UiTabs>
  </Page>
</template>

<style></style>
