import type { NavGroup } from '@/components/app-sidebar/types'

import {
  AppWindowIcon,
  BadgeHelpIcon,
  BanknoteIcon,
  BellDotIcon,
  Building2Icon,
  CalendarDaysIcon,
  CarIcon,
  ClockIcon,
  CreditCardIcon,
  DollarSignIcon,
  FilePenIcon,
  FileSpreadsheetIcon,
  FileTextIcon,
  FolderKanbanIcon,
  LayoutDashboardIcon,
  ListOrderedIcon,
  ListTodoIcon,
  PackageIcon,
  PaletteIcon,
  PictureInPicture2Icon,
  SettingsIcon,
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
          title: 'Vehicles',
          url: '/vehicles',
          icon: CarIcon,
        },
        {
          title: 'Equipment',
          url: '/equipment',
          icon: WrenchIcon,
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
          title: 'Teams',
          url: '/teams',
          icon: UsersIcon,
        },
        {
          title: 'Timesheets',
          url: '/timesheets',
          icon: ClockIcon,
        },
        {
          title: 'Planning',
          url: '/planning',
          icon: CalendarDaysIcon,
        },
        {
          title: 'Tasks',
          url: '/tasks',
          icon: ListTodoIcon,
        },
      ],
    },
    {
      title: 'Debtors',
      items: [
        {
          title: 'Customers',
          url: '/customers',
          icon: UserCircleIcon,
        },
        {
          title: 'Products',
          url: '/products',
          icon: PackageIcon,
        },
        {
          title: 'Production list',
          url: '/production-list',
          icon: ListOrderedIcon,
        },
        {
          title: 'Invoices',
          url: '/invoices',
          icon: FileTextIcon,
        },
      ],
    },
    {
      title: 'Creditors',
      items: [
        {
          title: 'Suppliers',
          url: '/suppliers',
          icon: Building2Icon,
        },
        {
          title: 'Purchase order',
          url: '/purchase-order',
          icon: FilePenIcon,
        },
        {
          title: 'Self billing',
          url: '/self-billing',
          icon: FileSpreadsheetIcon,
        },
      ],
    },
    {
      title: 'Accounting',
      items: [
        {
          title: 'Transactions',
          url: '/transactions',
          icon: CreditCardIcon,
        },
        {
          title: 'Balance',
          url: '/balance',
          icon: DollarSignIcon,
        },
        {
          title: 'Bank accounts',
          url: '/bank-accounts',
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
