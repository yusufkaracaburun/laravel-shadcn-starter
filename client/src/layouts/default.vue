<script setup lang="ts">
import { useEventListener } from '@vueuse/core'
import { useCookies } from '@vueuse/integrations/useCookies'
import {
  BadgeCheck,
  Bell,
  CreditCard,
  LogOut,
  MenuIcon,
  Search,
  Settings,
  Sparkles,
  UserRoundCog,
} from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import { computed, ref } from 'vue'

import type { User as SidebarUser } from '@/components/app-sidebar/types'

import AppSidebar from '@/components/app-sidebar/index.vue'
import CommandChangeTheme from '@/components/command-menu-panel/command-change-theme.vue'
import CommandToPage from '@/components/command-menu-panel/command-to-page.vue'
import ToggleTheme from '@/components/toggle-theme.vue'
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty'
import { useAuth } from '@/composables/use-auth'
import { cn } from '@/lib/utils'
import { useAuthStore } from '@/stores/auth.store'
import { useThemeStore } from '@/stores/theme.store'

const defaultOpen = useCookies(['sidebar:state'])
const themeStore = useThemeStore()
const { contentLayout } = storeToRefs(themeStore)

const authStore = useAuthStore()
const { user: authUser } = storeToRefs(authStore)
const { logout } = useAuth()

const route = useRoute()

const sidebarUser = computed<SidebarUser>(() => {
  if (authUser.value) {
    return {
      name: authUser.value.name,
      email: authUser.value.email,
      avatar: authUser.value.profile_photo_url || null,
    }
  }
  return {
    name: 'Guest',
    email: '',
    avatar: null,
  }
})

// Command menu state
const commandMenuOpen = ref(false)

useEventListener('keydown', (event: KeyboardEvent) => {
  if (event.key === 'k' && (event.metaKey || event.ctrlKey)) {
    event.preventDefault()
    commandMenuOpen.value = !commandMenuOpen.value
  }
})

const firstKey = computed(() => (navigator?.userAgent.includes('Mac OS') ? '⌘' : 'Ctrl'))

// Generate breadcrumbs from route
const breadcrumbs = computed(() => {
  const path = route.path
  const segments = path.split('/').filter(Boolean)

  const crumbs: Array<{ label: string, path: string }> = []

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
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ')

    // Map common paths to better labels
    const labelMap: Record<string, string> = {
      'dashboard': 'Overview',
      'users': 'Customers',
      'companies': 'Products',
      'settings': 'Settings',
      'account': 'Account',
      'notifications': 'Notifications',
      'appearance': 'Appearance',
      'display': 'Display',
      'billing': 'Billing',
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
  <UiSidebarProvider :default-open="defaultOpen.get('sidebar:state')">
    <AppSidebar />
    <UiSidebarInset
      class="w-full max-w-full peer-data-[state=collapsed]:w-[calc(100%-var(--sidebar-width-icon)-1rem)] peer-data-[state=expanded]:w-[calc(100%-var(--sidebar-width))]"
    >
      <header
        class="flex items-center gap-3 sm:gap-4 h-16 px-4 shrink-0 transition-[width,height] ease-linear border-b"
      >
        <!-- Left Section: Sidebar Toggle + Breadcrumbs -->
        <div class="flex items-center gap-3 sm:gap-4">
          <UiSidebarTrigger class="-ml-1" data-testid="default-layout_sidebar-toggle" />
          <UiSeparator orientation="vertical" class="h-6" />
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
        </div>

        <!-- Right Section: Search + Icons -->
        <div class="flex-1" />
        <div class="flex items-center gap-2">
          <!-- Search Bar -->
          <div
            class="relative flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground border border-border bg-muted/5 rounded-md min-w-[240px] cursor-pointer hover:bg-muted/10 transition-colors"
            data-testid="default-layout_search" @click="commandMenuOpen = true"
          >
            <Search class="size-4" />
            <span class="flex-1 text-xs">Search</span>
            <UiKbd class="text-xs">
              {{ firstKey }} K
            </UiKbd>
          </div>

          <!-- Theme Toggle -->
          <ToggleTheme />

          <!-- Settings Icon -->
          <UiButton
            variant="ghost" size="icon" data-testid="default-layout_settings-button"
            @click="$router.push('/settings')"
          >
            <Settings class="size-4" />
          </UiButton>

          <!-- User Avatar Dropdown -->
          <UiDropdownMenu>
            <UiDropdownMenuTrigger as-child>
              <UiButton variant="ghost" size="icon" class="rounded-full" data-testid="default-layout_user-menu">
                <UiAvatar class="size-8">
                  <UiAvatarImage v-if="sidebarUser.avatar" :src="sidebarUser.avatar" :alt="sidebarUser.name" />
                  <UiAvatarFallback>
                    {{ sidebarUser.name.split(' ').map(n => n[0]).join('').toUpperCase() || 'U' }}
                  </UiAvatarFallback>
                </UiAvatar>
              </UiButton>
            </UiDropdownMenuTrigger>
            <UiDropdownMenuContent class="w-56 rounded-lg" align="end" :side-offset="4">
              <UiDropdownMenuLabel class="p-0 font-normal">
                <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                  <UiAvatar class="size-8 rounded-lg">
                    <UiAvatarImage v-if="sidebarUser.avatar" :src="sidebarUser.avatar" :alt="sidebarUser.name" />
                    <UiAvatarFallback class="rounded-lg">
                      {{ sidebarUser.name.split(' ').map(n => n[0]).join('').toUpperCase() || 'U' }}
                    </UiAvatarFallback>
                  </UiAvatar>
                  <div class="grid flex-1 text-sm leading-tight text-left">
                    <span class="font-semibold truncate">{{ sidebarUser.name }}</span>
                    <span class="text-xs truncate">{{ sidebarUser.email }}</span>
                  </div>
                </div>
              </UiDropdownMenuLabel>

              <UiDropdownMenuSeparator />
              <UiDropdownMenuGroup>
                <UiDropdownMenuItem @click="$router.push('/billing/')">
                  <Sparkles />
                  Upgrade to Pro
                </UiDropdownMenuItem>
              </UiDropdownMenuGroup>

              <UiDropdownMenuSeparator />
              <UiDropdownMenuGroup>
                <UiDropdownMenuItem @click="$router.push('/billing?type=billing')">
                  <CreditCard />
                  Billing
                </UiDropdownMenuItem>
              </UiDropdownMenuGroup>

              <UiDropdownMenuSeparator />
              <UiDropdownMenuGroup>
                <UiDropdownMenuItem @click="$router.push('/settings/')">
                  <UserRoundCog />
                  Profile
                </UiDropdownMenuItem>
                <UiDropdownMenuItem @click="$router.push('/settings/account')">
                  <BadgeCheck />
                  Account
                </UiDropdownMenuItem>
                <UiDropdownMenuItem @click="$router.push('/settings/notifications')">
                  <Bell />
                  Notifications
                </UiDropdownMenuItem>
              </UiDropdownMenuGroup>

              <UiDropdownMenuSeparator />
              <UiDropdownMenuItem data-testid="default-layout_logout_button" @click="logout">
                <LogOut />
                Log out
              </UiDropdownMenuItem>
            </UiDropdownMenuContent>
          </UiDropdownMenu>
        </div>

        <!-- Command Menu Dialog -->
        <UiCommandDialog v-model:open="commandMenuOpen">
          <UiCommandInput placeholder="Type a command or search..." />
          <UiCommandList>
            <UiCommandEmpty>
              <Empty>
                <EmptyHeader>
                  <EmptyMedia variant="icon">
                    <MenuIcon />
                  </EmptyMedia>
                  <EmptyTitle>No menu found.</EmptyTitle>
                  <EmptyDescription>
                    Try searching for a command or check the spelling.
                  </EmptyDescription>
                </EmptyHeader>
              </Empty>
            </UiCommandEmpty>
            <CommandToPage @click="commandMenuOpen = false" />
            <UiCommandSeparator />
            <CommandChangeTheme @click="commandMenuOpen = false" />
          </UiCommandList>
        </UiCommandDialog>
      </header>

      <div :class="cn('p-4 grow', contentLayout === 'centered' ? 'container mx-auto ' : '')">
        <router-view />
      </div>
    </UiSidebarInset>
  </UiSidebarProvider>
</template>
