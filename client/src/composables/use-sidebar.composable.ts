import {
  AppWindow,
  BadgeHelp,
  BellDot,
  Bug,
  Building2,
  Component,
  CreditCard,
  FileText,
  FolderKanban,
  LayoutDashboard,
  ListTodo,
  Megaphone,
  Package,
  Palette,
  PictureInPicture2,
  Podcast,
  Settings,
  SquareUserRound,
  User,
  UserCircle,
  Users,
  Wrench,
} from 'lucide-vue-next'

import type { NavGroup } from '@/components/app-sidebar/types'

export function useSidebar() {
  const navData = ref<NavGroup[]>()

  navData.value = [
    {
      title: 'General',
      items: [
        {
          title: 'Dashboard',
          url: '/dashboard',
          icon: LayoutDashboard,
        },
      ],
    },
    {
      title: 'Pages',
      items: [
        {
          title: 'Users',
          url: '/users',
          icon: Users,
        },
        {
          title: 'Companies',
          url: '/companies',
          icon: Building2,
        },
        {
          title: 'Customers',
          url: '/customers',
          icon: UserCircle,
        },
        {
          title: 'Projects',
          url: '/projects',
          icon: FolderKanban,
        },
        {
          title: 'Items',
          url: '/items',
          icon: Package,
        },
        {
          title: 'Tasks',
          url: '/tasks',
          icon: ListTodo,
        },
        {
          title: 'Invoices',
          url: '/invoices',
          icon: FileText,
        },
        {
          title: 'Apps',
          url: '/apps',
          icon: AppWindow,
        },
      ],
    },
    {
      title: 'Example',
      items: [
        {
          title: 'Ai Talk Example',
          url: '/ai-talk',
          icon: Podcast,
        },
        {
          title: 'Marketing',
          url: '/marketing',
          icon: Megaphone,
        },
        {
          title: 'Auth',
          icon: SquareUserRound,
          items: [
            { title: 'Sign In', url: '/auth/sign-in' },
            { title: 'Sign In(2 Col)', url: '/auth/sign-in-2' },
            { title: 'Sign Up', url: '/auth/sign-up' },
            { title: 'Forgot Password', url: '/auth/forgot-password' },
            { title: 'OTP', url: '/auth/otp' },
          ],
        },
        {
          title: 'Errors',
          icon: Bug,
          items: [
            { title: '401 | Unauthorized', url: '/errors/401' },
            { title: '403 | Forbidden', url: '/errors/403' },
            { title: '404 | Not Found', url: '/errors/404' },
            { title: '500 | Internal Server Error', url: '/errors/500' },
            { title: '503 | Maintenance Error', url: '/errors/503' },
          ],
        },
      ],
    },
    {
      title: 'Other',
      items: [
        {
          title: 'Settings',
          icon: Settings,
          items: [
            { title: 'Profile', url: '/settings/', icon: User },
            { title: 'Account', url: '/settings/account', icon: Wrench },
            { title: 'Appearance', url: '/settings/appearance', icon: Palette },
            {
              title: 'Notifications',
              url: '/settings/notifications',
              icon: BellDot,
            },
            {
              title: 'Display',
              url: '/settings/display',
              icon: PictureInPicture2,
            },
          ],
        },
        {
          title: 'SVA Components',
          url: '/sva-components',
          icon: Component,
        },
        {
          title: 'Help Center',
          url: '/help-center',
          icon: BadgeHelp,
        },
      ],
    },
  ]

  const otherPages = ref<NavGroup[]>([
    {
      title: 'Other',
      items: [
        {
          title: 'Plans & Pricing',
          icon: CreditCard,
          url: '/billing',
        },
      ],
    },
  ])

  return {
    navData,
    otherPages,
  }
}
