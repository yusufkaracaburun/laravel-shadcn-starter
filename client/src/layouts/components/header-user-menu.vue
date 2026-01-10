<script setup lang="ts">
import {
  BadgeCheck,
  Bell,
  CreditCard,
  LogOut,
  Sparkles,
  UserRoundCog,
} from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import { computed } from 'vue'

import type { User as SidebarUser } from '@/components/app-sidebar/types'

import { useAuth } from '@/pages/auth/composables/use-auth.composable'
import { useAuthStore } from '@/stores/auth.store'

const authStore = useAuthStore()
const { user: authUser } = storeToRefs(authStore)
const { logout } = useAuth()

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
</script>

<template>
  <UiDropdownMenu>
    <UiDropdownMenuTrigger as-child>
      <UiButton
        variant="ghost"
        size="icon"
        class="rounded-full"
        data-testid="default-layout_user-menu"
      >
        <UiAvatar class="size-8">
          <UiAvatarImage
            v-if="sidebarUser.avatar"
            :src="sidebarUser.avatar"
            :alt="sidebarUser.name"
          />
          <UiAvatarFallback>
            {{
              sidebarUser.name
                .split(' ')
                .map((n: string) => n[0])
                .join('')
                .toUpperCase() || 'U'
            }}
          </UiAvatarFallback>
        </UiAvatar>
      </UiButton>
    </UiDropdownMenuTrigger>
    <UiDropdownMenuContent class="w-56 rounded-lg" align="end" :side-offset="4">
      <UiDropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
          <UiAvatar class="size-8 rounded-lg">
            <UiAvatarImage
              v-if="sidebarUser.avatar"
              :src="sidebarUser.avatar"
              :alt="sidebarUser.name"
            />
            <UiAvatarFallback class="rounded-lg">
              {{
                sidebarUser.name
                  .split(' ')
                  .map((n: string) => n[0])
                  .join('')
                  .toUpperCase() || 'U'
              }}
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
      <UiDropdownMenuItem
        data-testid="default-layout_logout_button"
        @click="logout"
      >
        <LogOut />
        Log out
      </UiDropdownMenuItem>
    </UiDropdownMenuContent>
  </UiDropdownMenu>
</template>
