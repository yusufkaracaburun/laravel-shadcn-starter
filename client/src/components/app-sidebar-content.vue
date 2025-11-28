<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { RouterLink, useRoute } from 'vue-router'
import { useColorMode } from '@vueuse/core'
import { computed } from 'vue'

import { useRouteHelper } from '@/composables/use-route-helper'

const { route: routeName, routeCurrent } = useRouteHelper()
const currentRoute = useRoute()
const mode = useColorMode({
  attribute: 'class',
  modes: { light: '', dark: 'dark' },
})

interface NavItem {
  name: string
  icon: string
  route?: string
  href?: string
  external?: boolean
}

interface NavGroup {
  label: string | null
  items: NavItem[]
  class?: string
}

const navigationConfig: NavGroup[] = [
  {
    label: 'Platform',
    items: [
      {
        name: 'Dashboard',
        icon: 'lucide:layout-dashboard',
        route: 'dashboard',
      },
      {
        name: 'Settings',
        icon: 'lucide:settings',
        route: 'profile.show',
      },
      {
        name: 'Chat',
        icon: 'lucide:message-circle',
        route: 'chat.index',
      },
    ],
  },
  {
    label: 'API',
    items: [
      {
        name: 'API Tokens',
        icon: 'lucide:key',
        route: 'api-tokens.index',
      },
      {
        name: 'API Documentation',
        icon: 'lucide:book-heart',
        route: 'scribe',
        external: true,
      },
    ],
  },
  {
    label: null,
    class: 'mt-auto',
    items: [
      {
        name: 'Support',
        icon: 'lucide:life-buoy',
        href: 'https://github.com/shipfastlabs/larasonic-vue/issues',
        external: true,
      },
      {
        name: 'Documentation',
        icon: 'lucide:book-marked',
        href: 'https://docs.larasonic.com',
        external: true,
      },
    ],
  },
]

const isDarkMode = computed(() => mode.value === 'dark')

function renderLink(item: NavItem) {
  if (item.external) {
    return {
      is: 'a',
      href: item.href || (item.route ? routeName(item.route) : '#'),
      target: '_blank',
      rel: 'noopener noreferrer',
    }
  }
  return {
    is: RouterLink,
    to: item.route ? { name: item.route } : { path: item.href || '#' },
  }
}
</script>

<template>
  <UiSidebarContent>
    <UiSidebarGroup
      v-for="(group, index) in navigationConfig"
      :key="index"
      :class="group.class"
    >
      <UiSidebarGroupLabel v-if="group.label">
        {{ group.label }}
      </UiSidebarGroupLabel>
      <UiSidebarMenu>
        <UiSidebarMenuItem
          v-for="item in group.items"
          :key="item.name"
          :class="{
            'font-semibold text-primary bg-secondary rounded':
              !item.external && item.route && routeCurrent(item.route),
          }"
        >
          <UiSidebarMenuButton as-child>
            <component
              v-bind="renderLink(item)"
              :is="item.external ? 'a' : RouterLink"
            >
              <Icon :icon="item.icon" />
              {{ item.name }}
            </component>
          </UiSidebarMenuButton>
        </UiSidebarMenuItem>
        <UiSidebarMenuItem v-if="index === navigationConfig.length - 1">
          <UiSidebarMenuButton
            @click="mode = isDarkMode ? 'light' : 'dark'"
          >
            <Icon
              :icon="isDarkMode ? 'lucide:moon' : 'lucide:sun'"
            />
            {{ isDarkMode ? 'Dark' : 'Light' }} Mode
          </UiSidebarMenuButton>
        </UiSidebarMenuItem>
      </UiSidebarMenu>
    </UiSidebarGroup>
  </UiSidebarContent>
</template>

