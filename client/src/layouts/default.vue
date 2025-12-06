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

// Notification state
interface Notification {
  id: number
  title: string
  message: string
  time: string
  read: boolean
  type: string
}

const notificationOpen = ref(false)
const unreadCount = ref(3) // Mock unread count

// Mock notifications data
const notifications = ref<Notification[]>([
  {
    id: 1,
    title: 'New message received',
    message: 'You have a new message from John Doe',
    time: '2 minutes ago',
    read: false,
    type: 'message',
  },
  {
    id: 2,
    title: 'Task assigned',
    message: 'A new task has been assigned to you',
    time: '1 hour ago',
    read: false,
    type: 'task',
  },
  {
    id: 3,
    title: 'System update',
    message: 'Your system has been updated successfully',
    time: '3 hours ago',
    read: false,
    type: 'system',
  },
  {
    id: 4,
    title: 'Payment received',
    message: 'Payment of $500 has been received',
    time: '1 day ago',
    read: true,
    type: 'payment',
  },
])

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

// Notification handlers
function markAsRead(id: number) {
  const notification = notifications.value.find(n => n.id === id)
  if (notification && !notification.read) {
    notification.read = true
    unreadCount.value = Math.max(0, unreadCount.value - 1)
  }
}

function markAllAsRead() {
  notifications.value.forEach((notification) => {
    if (!notification.read) {
      notification.read = true
    }
  })
  unreadCount.value = 0
}
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

          <!-- Notifications Dropdown -->
          <UiDropdownMenu v-model:open="notificationOpen">
            <UiDropdownMenuTrigger as-child>
              <UiButton
                variant="ghost"
                size="icon"
                class="relative"
                data-testid="default-layout_notifications-button"
              >
                <Bell class="size-4" />
                <UiBadge
                  v-if="unreadCount > 0"
                  variant="destructive"
                  class="absolute -top-1 -right-1 h-5 min-w-5 px-1 text-[10px] flex items-center justify-center"
                >
                  {{ unreadCount > 9 ? '9+' : unreadCount }}
                </UiBadge>
              </UiButton>
            </UiDropdownMenuTrigger>
            <UiDropdownMenuContent class="w-80 rounded-lg" align="end" :side-offset="4">
              <UiDropdownMenuLabel class="flex items-center justify-between px-3 py-2">
                <span class="font-semibold">Notifications</span>
                <UiButton
                  v-if="unreadCount > 0"
                  variant="ghost"
                  size="sm"
                  class="h-7 text-xs"
                  @click.stop="markAllAsRead"
                >
                  Mark all as read
                </UiButton>
              </UiDropdownMenuLabel>
              <UiDropdownMenuSeparator />
              <div class="max-h-[400px] overflow-y-auto">
                <UiDropdownMenuItem
                  v-for="notification in notifications"
                  :key="notification.id"
                  class="flex flex-col items-start gap-1 px-3 py-2 cursor-pointer"
                  :class="!notification.read && 'bg-muted/50'"
                  @click="markAsRead(notification.id)"
                >
                  <div class="flex items-start justify-between w-full gap-2">
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium truncate">
                        {{ notification.title }}
                      </p>
                      <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">
                        {{ notification.message }}
                      </p>
                      <p class="text-xs text-muted-foreground mt-1">
                        {{ notification.time }}
                      </p>
                    </div>
                    <div
                      v-if="!notification.read"
                      class="h-2 w-2 rounded-full bg-primary shrink-0 mt-1"
                    />
                  </div>
                </UiDropdownMenuItem>
                <UiDropdownMenuItem
                  v-if="notifications.length === 0"
                  class="flex items-center justify-center py-8 text-muted-foreground"
                >
                  No notifications
                </UiDropdownMenuItem>
              </div>
              <UiDropdownMenuSeparator />
              <UiDropdownMenuItem
                class="justify-center cursor-pointer"
                @click="$router.push('/settings/notifications')"
              >
                View all notifications
              </UiDropdownMenuItem>
            </UiDropdownMenuContent>
          </UiDropdownMenu>

          <!-- User Avatar Dropdown -->
          <UiDropdownMenu>
            <UiDropdownMenuTrigger as-child>
              <UiButton variant="ghost" size="icon" class="rounded-full" data-testid="default-layout_user-menu">
                <UiAvatar class="size-8">
                  <UiAvatarImage v-if="sidebarUser.avatar" :src="sidebarUser.avatar" :alt="sidebarUser.name" />
                  <UiAvatarFallback>
                    {{ sidebarUser.name.split(' ').map((n: string) => n[0]).join('').toUpperCase() || 'U' }}
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
                      {{ sidebarUser.name.split(' ').map((n: string) => n[0]).join('').toUpperCase() || 'U' }}
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
