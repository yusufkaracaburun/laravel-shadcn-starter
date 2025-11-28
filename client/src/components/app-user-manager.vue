<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { RouterLink, useRouter } from 'vue-router'
import { computed } from 'vue'

import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)

async function logout() {
  await authStore.logout()
  router.push({ path: '/auth/sign-in' })
}
</script>

<template>
  <UiDropdownMenu>
    <UiDropdownMenuTrigger as-child>
      <UiSidebarMenuButton
        size="lg"
        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
      >
        <UiAvatar class="h-8 w-8 rounded-lg">
          <UiAvatarImage
            :src="user?.profile_photo_path ?? ''"
            :alt="user?.name ?? ''"
          />
          <UiAvatarFallback class="rounded-lg">
            {{ user?.name?.charAt(0) ?? 'U' }}
          </UiAvatarFallback>
        </UiAvatar>
        <div class="grid flex-1 text-left text-sm leading-tight">
          <span class="truncate font-semibold">{{
            user?.name ?? 'User'
          }}</span>
          <span class="truncate text-xs">{{
            user?.email ?? ''
          }}</span>
        </div>
        <Icon icon="lucide:chevrons-up-down" class="ml-auto size-4" />
      </UiSidebarMenuButton>
    </UiDropdownMenuTrigger>
    <UiDropdownMenuContent
      class="w-[--radix-dropdown-menu-trigger-width] min-w-56 rounded-lg"
      side="bottom"
      align="end"
      :side-offset="4"
    >
      <UiDropdownMenuLabel class="p-0 font-normal">
        <div
          class="flex items-center gap-2 px-1 py-1.5 text-left text-sm"
        >
          <UiAvatar class="h-8 w-8 rounded-lg">
            <UiAvatarImage
              :src="user?.profile_photo_path ?? ''"
              :alt="user?.name ?? ''"
            />
            <UiAvatarFallback class="rounded-lg">
              {{ user?.name?.charAt(0) ?? 'U' }}
            </UiAvatarFallback>
          </UiAvatar>
          <div class="grid flex-1 text-left text-sm leading-tight">
            <span class="truncate font-semibold">{{
              user?.name ?? 'User'
            }}</span>
            <span class="truncate text-xs">{{
              user?.email ?? ''
            }}</span>
          </div>
        </div>
      </UiDropdownMenuLabel>
      <UiDropdownMenuSeparator />
      <UiDropdownMenuGroup>
        <UiDropdownMenuItem as-child>
          <RouterLink :to="{ name: 'profile.show' }">
            <Icon icon="lucide:settings" />
            Settings
          </RouterLink>
        </UiDropdownMenuItem>
        <UiDropdownMenuItem as-child>
          <RouterLink :to="{ name: 'api-tokens.index' }">
            <Icon icon="lucide:key" />
            API Tokens
          </RouterLink>
        </UiDropdownMenuItem>
        <UiDropdownMenuItem as-child>
          <RouterLink :to="{ name: 'subscriptions.create' }">
            <Icon icon="lucide:credit-card" />
            Billing
          </RouterLink>
        </UiDropdownMenuItem>
      </UiDropdownMenuGroup>
      <UiDropdownMenuSeparator />
      <UiDropdownMenuItem @click="logout">
        <Icon icon="lucide:log-out" />
        Log out
      </UiDropdownMenuItem>
    </UiDropdownMenuContent>
  </UiDropdownMenu>
</template>

