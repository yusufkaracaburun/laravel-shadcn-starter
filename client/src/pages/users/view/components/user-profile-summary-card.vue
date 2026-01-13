<script setup lang="ts">
import type { IUser } from '@/pages/users/models/users'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import {
  Card,
  CardContent,
} from '@/components/ui/card'
import {
  MailIcon,
  PhoneIcon,
} from '@/composables/use-icons.composable'
import { MapPin, Link } from 'lucide-vue-next'

interface Props {
  user: IUser
  initials: string
}

const props = defineProps<Props>()

// Mock data for stats - in real app, these would come from API
const stats = {
  posts: 184,
  projects: 32,
  members: '4.5K',
}

// Mock data for contact info - in real app, these would come from user profile
const contactInfo = {
  email: props.user.email,
  phone: '(+1-876) 8654 239 581',
  location: 'Canada',
  websites: [
    'https://shadcnuikit.com',
    'https://bundui.io/',
  ],
}
</script>

<template>
  <Card>
    <CardContent class="pt-6">
      <div class="flex flex-col items-center text-center space-y-4">
        <!-- Avatar -->
        <Avatar class="size-24 ring-4 ring-background shadow-lg">
          <AvatarImage
            v-if="user.profile_photo_url"
            :src="user.profile_photo_url"
            :alt="user.name"
          />
          <AvatarFallback
            class="text-3xl font-semibold bg-primary text-primary-foreground"
          >
            {{ initials }}
          </AvatarFallback>
        </Avatar>

        <!-- Name and Badge -->
        <div class="flex items-center gap-2">
          <h2 class="text-2xl font-bold">
            {{ user.name }}
          </h2>
          <Badge variant="default" class="bg-blue-500 text-white">
            Pro
          </Badge>
        </div>

        <!-- Title -->
        <p class="text-muted-foreground">
          Project Manager
        </p>

        <!-- Stats -->
        <div class="flex items-center gap-6 pt-2">
          <div class="text-center">
            <div class="text-xl font-bold">
              {{ stats.posts }}
            </div>
            <div class="text-sm text-muted-foreground">
              Post
            </div>
          </div>
          <div class="text-center">
            <div class="text-xl font-bold">
              {{ stats.projects }}
            </div>
            <div class="text-sm text-muted-foreground">
              Projects
            </div>
          </div>
          <div class="text-center">
            <div class="text-xl font-bold">
              {{ stats.members }}
            </div>
            <div class="text-sm text-muted-foreground">
              Members
            </div>
          </div>
        </div>

        <!-- Contact Info -->
        <div class="w-full space-y-3 pt-4 border-t">
          <div class="flex items-center gap-2 text-sm">
            <MailIcon class="size-4 text-muted-foreground" />
            <span class="text-muted-foreground">{{ contactInfo.email }}</span>
          </div>
          <div class="flex items-center gap-2 text-sm">
            <PhoneIcon class="size-4 text-muted-foreground" />
            <span class="text-muted-foreground">{{ contactInfo.phone }}</span>
          </div>
          <div class="flex items-center gap-2 text-sm">
            <MapPin class="size-4 text-muted-foreground" />
            <span class="text-muted-foreground">{{ contactInfo.location }}</span>
          </div>
          <div
            v-for="(website, index) in contactInfo.websites"
            :key="index"
            class="flex items-center gap-2 text-sm"
          >
            <Link class="size-4 text-muted-foreground" />
            <a
              :href="website"
              target="_blank"
              rel="noopener noreferrer"
              class="text-muted-foreground hover:underline"
            >
              {{ website }}
            </a>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
