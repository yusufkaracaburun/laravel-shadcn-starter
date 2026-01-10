import {
  AppWindow,
  BadgeHelp,
  BellDot,
  Bug,
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
      title: 'User management',
      items: [
        {
          title: 'Users',
          url: '/users',
          icon: Users,
        },
        {
          title: 'Customers',
          url: '/customers',
          icon: UserCircle,
        },
      ],
    },
    {
      title: 'Project management',
      items: [
        {
          title: 'Projects',
          url: '/projects',
          icon: FolderKanban,
        },
        {
          title: 'Tasks',
          url: '/tasks',
          icon: ListTodo,
        },
      ],
    },
    {
      title: 'Accounting',
      items: [
        {
          title: 'Items',
          url: '/items',
          icon: Package,
        },
        {
          title: 'Invoices',
          url: '/invoices',
          icon: FileText,
        },
      ],
    },
    {
      title: 'System',
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
            {
              title: 'Apps',
              url: '/apps',
              icon: AppWindow,
            },
          ],
        },
        {
          title: 'Help Center',
          url: '/help-center',
          icon: BadgeHelp,
        },
      ],
    },
    {
      title: 'Example',
      items: [
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
          title: 'SVA Components',
          url: '/sva-components',
          icon: Component,
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
