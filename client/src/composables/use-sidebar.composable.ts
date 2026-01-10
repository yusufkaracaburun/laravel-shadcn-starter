import type { NavGroup } from '@/components/app-sidebar/types'

import {
  AppWindowIcon,
  BadgeHelpIcon,
  BanknoteIcon,
  BellDotIcon,
  BugIcon,
  Building2Icon,
  ComponentIcon,
  CreditCardIcon,
  DollarSignIcon,
  FileTextIcon,
  FolderKanbanIcon,
  LayoutDashboardIcon,
  ListOrderedIcon,
  ListTodoIcon,
  MegaphoneIcon,
  PackageIcon,
  PaletteIcon,
  PictureInPicture2Icon,
  PodcastIcon,
  ReceiptIcon,
  SettingsIcon,
  ShoppingCartIcon,
  SquareUserRoundIcon,
  UserCircleIcon,
  UserIcon,
  UsersIcon,
  WrenchIcon,
} from '@/composables/use-icons.composable'

export function useSidebar() {
  const navData = ref<NavGroup[]>()

  navData.value = [
    {
      title: 'General',
      items: [
        {
          title: 'Dashboard',
          url: '/dashboard',
          icon: LayoutDashboardIcon,
        },
      ],
    },
    {
      title: 'System management',
      items: [
        {
          title: 'Users',
          url: '/users',
          icon: UsersIcon,
        },
        {
          title: 'Customers',
          url: '/customers',
          icon: UserCircleIcon,
        },
        {
          title: 'Suppliers',
          url: '/customers',
          icon: Building2Icon,
        },
        {
          title: 'Items',
          url: '/items',
          icon: PackageIcon,
        },
      ],
    },
    {
      title: 'Project management',
      items: [
        {
          title: 'Projects',
          url: '/projects',
          icon: FolderKanbanIcon,
        },
        {
          title: 'Tasks',
          url: '/tasks',
          icon: ListTodoIcon,
        },
      ],
    },
    {
      title: 'Finance',
      items: [
        {
          title: 'Production list',
          url: '/invoices',
          icon: ListOrderedIcon,
        },
        {
          title: 'Invoices',
          url: '/invoices',
          icon: FileTextIcon,
        },
        {
          title: 'Purchase order',
          url: '/invoices',
          icon: ShoppingCartIcon,
        },
        {
          title: 'Self billing',
          url: '/invoices',
          icon: ReceiptIcon,
        },
      ],
    },
    {
      title: 'Accounting',
      items: [
        {
          title: 'Transactions',
          url: '/invoices',
          icon: CreditCardIcon,
        },
        {
          title: 'Balance',
          url: '/invoices',
          icon: DollarSignIcon,
        },
        {
          title: 'Bank accounts',
          url: '/invoices',
          icon: BanknoteIcon,
        },
      ],
    },
    {
      title: 'Configuration',
      items: [
        {
          title: 'Settings',
          icon: SettingsIcon,
          items: [
            { title: 'Profile', url: '/settings/', icon: UserIcon },
            { title: 'Account', url: '/settings/account', icon: WrenchIcon },
            {
              title: 'Appearance',
              url: '/settings/appearance',
              icon: PaletteIcon,
            },
            {
              title: 'Notifications',
              url: '/settings/notifications',
              icon: BellDotIcon,
            },
            {
              title: 'Display',
              url: '/settings/display',
              icon: PictureInPicture2Icon,
            },
            {
              title: 'Apps',
              url: '/apps',
              icon: AppWindowIcon,
            },
          ],
        },
        {
          title: 'Help Center',
          url: '/help-center',
          icon: BadgeHelpIcon,
        },
      ],
    },
    // {
    //   title: 'Example',
    //   items: [
    //     {
    //       title: 'Auth',
    //       icon: SquareUserRoundIcon,
    //       items: [
    //         { title: 'Sign In', url: '/auth/sign-in' },
    //         { title: 'Sign In(2 Col)', url: '/auth/sign-in-2' },
    //         { title: 'Sign Up', url: '/auth/sign-up' },
    //         { title: 'Forgot Password', url: '/auth/forgot-password' },
    //         { title: 'OTP', url: '/auth/otp' },
    //       ],
    //     },
    //     {
    //       title: 'Errors',
    //       icon: BugIcon,
    //       items: [
    //         { title: '401 | Unauthorized', url: '/errors/401' },
    //         { title: '403 | Forbidden', url: '/errors/403' },
    //         { title: '404 | Not Found', url: '/errors/404' },
    //         { title: '500 | Internal Server Error', url: '/errors/500' },
    //         { title: '503 | Maintenance Error', url: '/errors/503' },
    //       ],
    //     },
    //     {
    //       title: 'Ai Talk Example',
    //       url: '/ai-talk',
    //       icon: PodcastIcon,
    //     },
    //     {
    //       title: 'Marketing',
    //       url: '/marketing',
    //       icon: MegaphoneIcon,
    //     },
    //     {
    //       title: 'SVA Components',
    //       url: '/sva-components',
    //       icon: ComponentIcon,
    //     },
    //   ],
    // },
  ]

  const otherPages = ref<NavGroup[]>([
    {
      title: 'Other',
      items: [
        {
          title: 'Plans & Pricing',
          icon: CreditCardIcon,
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
