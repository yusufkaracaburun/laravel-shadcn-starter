<script setup lang="ts">
import { computed } from 'vue'

const route = useRoute()

// Generate breadcrumbs from route
const breadcrumbs = computed(() => {
  const path = route.path
  const segments = path.split('/').filter(Boolean)

  const crumbs: Array<{ label: string; path: string }> = []

  // Always start with Overview (Dashboard) as first breadcrumb
  if (segments.length === 0 || segments[0] === 'dashboard') {
    crumbs.push({ label: 'Overview', path: '/dashboard' })
    return crumbs
  }

  // Add Overview as first breadcrumb for other pages
  crumbs.push({ label: 'Overview', path: '/dashboard' })

  // Build breadcrumbs from path segments
  let currentPath = ''
  for (const segment of segments) {
    currentPath += `/${segment}`

    // Capitalize and format label
    let label = segment
      .split('-')
      .map((word: string) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ')

    // Map common paths to better labels
    const labelMap: Record<string, string> = {
      dashboard: 'Overview',
      users: 'Customers',
      companies: 'Products',
      settings: 'Settings',
      account: 'Account',
      notifications: 'Notifications',
      appearance: 'Appearance',
      display: 'Display',
      billing: 'Billing',
      'sign-in': 'Sign In',
      'sign-up': 'Sign Up',
      'forgot-password': 'Forgot Password',
    }

    if (labelMap[segment]) {
      label = labelMap[segment]
    }

    crumbs.push({ label, path: currentPath })
  }

  return crumbs
})
</script>

<template>
  <UiBreadcrumb v-if="breadcrumbs.length > 0">
    <UiBreadcrumbList>
      <UiBreadcrumbItem v-for="(crumb, index) in breadcrumbs" :key="crumb.path">
        <UiBreadcrumbLink
          v-if="index < breadcrumbs.length - 1"
          :to="crumb.path"
          data-testid="default-layout_breadcrumb-link"
        >
          {{ crumb.label }}
        </UiBreadcrumbLink>
        <UiBreadcrumbPage v-else data-testid="default-layout_breadcrumb-page">
          {{ crumb.label }}
        </UiBreadcrumbPage>
        <UiBreadcrumbSeparator v-if="index < breadcrumbs.length - 1" />
      </UiBreadcrumbItem>
    </UiBreadcrumbList>
  </UiBreadcrumb>
</template>
