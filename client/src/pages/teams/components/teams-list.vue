<script setup lang="ts">
import { Users } from 'lucide-vue-next'
import Draggable from 'vuedraggable'

import type { ITeam } from '@/pages/teams/models/teams'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { cn } from '@/lib/utils'
import UserDetailsDialog from './user-details-dialog.vue'

interface Props {
  teams: ITeam[]
  loading?: boolean
  isDragging?: boolean
  loadingTeamId?: number | null
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  isDragging: false,
  loadingTeamId: null,
})

const emit = defineEmits<{
  userDropped: [teamId: number, userId: number]
  dragEnter: [teamId: number]
  dragLeave: [teamId: number]
}>()

const draggedOverTeamId = ref<number | null>(null)
const teamDropZones = ref<Record<number, any[]>>({})
const selectedUserId = ref<number | null>(null)
const isUserDialogOpen = ref(false)

// Initialize empty arrays for each team
watch(
  () => props.teams,
  (teams) => {
    teams.forEach((team) => {
      if (!teamDropZones.value[team.id]) {
        teamDropZones.value[team.id] = []
      }
    })
  },
  { immediate: true },
)

function onDragEnter(teamId: number) {
  draggedOverTeamId.value = teamId
  emit('dragEnter', teamId)
}

function onDragLeave(teamId: number) {
  if (draggedOverTeamId.value === teamId) {
    draggedOverTeamId.value = null
  }
  emit('dragLeave', teamId)
}

function onAdd(
  teamId: number,
  event: {
    added?: { element?: { id: number } }
    item?: { element?: { id: number } }
  },
) {
  const user = event.added?.element || event.item?.element
  if (user?.id) {
    // Clear the drop zone immediately
    teamDropZones.value[teamId] = []
    emit('userDropped', teamId, user.id)
  }
  draggedOverTeamId.value = null
}

function getTeamMembers(team: ITeam) {
  // Backend returns users array when include=users is used
  const members = team.users || []
  return Array.isArray(members) ? members : []
}

function getInitials(name: string): string {
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

function showUserDetails(userId: number) {
  selectedUserId.value = userId
  isUserDialogOpen.value = true
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold">Teams</h2>
      <Badge variant="secondary"> {{ teams.length }} teams </Badge>
    </div>

    <div v-if="loading && teams.length === 0" class="space-y-4">
      <Card v-for="i in 3" :key="i" class="animate-pulse">
        <CardHeader>
          <div class="h-4 bg-muted rounded w-1/3" />
        </CardHeader>
        <CardContent>
          <div class="h-3 bg-muted rounded w-1/2" />
        </CardContent>
      </Card>
    </div>

    <div v-else-if="teams.length === 0" class="text-center py-12">
      <Users class="mx-auto size-12 text-muted-foreground mb-4" />
      <p class="text-muted-foreground">No teams found</p>
    </div>

    <div v-else class="space-y-3">
      <Card
        v-for="team in teams"
        :key="team.id"
        :class="
          cn(
            'transition-all duration-200 min-h-[120px]',
            draggedOverTeamId === team.id
              ? 'ring-2 ring-primary ring-offset-2 bg-accent border-primary'
              : 'hover:bg-accent/50',
            loadingTeamId === team.id && 'opacity-50',
          )
        "
      >
        <CardHeader class="pb-3">
          <div class="flex items-center justify-between">
            <CardTitle class="text-base">
              {{ team.name }}
            </CardTitle>
            <Badge
              v-if="team.personal_team"
              variant="secondary"
              class="text-xs"
            >
              Personal
            </Badge>
          </div>
          <CardDescription class="text-xs">
            Team ID: {{ team.id }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <!-- Team Members Avatars -->
          <div v-if="getTeamMembers(team).length > 0" class="mb-3 space-y-2">
            <div class="flex items-center gap-1.5 flex-wrap">
              <Avatar
                v-for="member in getTeamMembers(team)"
                :key="member.id"
                class="size-9 cursor-pointer border-2 border-background hover:ring-2 hover:ring-primary hover:scale-110 transition-all shadow-sm"
                @click="showUserDetails(member.id)"
              >
                <AvatarImage
                  v-if="member.profile_photo_url"
                  :src="member.profile_photo_url"
                  :alt="member.name"
                />
                <AvatarFallback class="text-xs font-medium">
                  {{ getInitials(member.name) }}
                </AvatarFallback>
              </Avatar>
            </div>
            <div class="text-xs text-muted-foreground">
              {{ team.users_count }}
              {{ team.users_count === 1 ? 'member' : 'members' }}
            </div>
          </div>

          <Draggable
            :model-value="teamDropZones[team.id] || []"
            :group="{ name: 'team-users', put: true, pull: false }"
            item-key="id"
            class="min-h-[60px]"
            @add="
              (event: {
                added?: { element?: { id: number } }
                item?: { element?: { id: number } }
              }) => onAdd(team.id, event)
            "
            @enter="() => onDragEnter(team.id)"
            @leave="() => onDragLeave(team.id)"
          >
            <template #item>
              <!-- Users dropped here will be handled -->
            </template>
            <div
              class="flex items-center gap-2 text-sm text-muted-foreground min-h-[60px] border-2 border-dashed rounded-lg p-4 transition-colors"
              :class="
                draggedOverTeamId === team.id
                  ? 'border-primary bg-primary/5'
                  : 'border-muted'
              "
            >
              <Users class="size-4" />
              <span>Drop users here</span>
            </div>
          </Draggable>
          <div
            v-if="loadingTeamId === team.id"
            class="mt-2 text-xs text-muted-foreground flex items-center gap-2"
          >
            <div
              class="size-3 border-2 border-primary border-t-transparent rounded-full animate-spin"
            />
            Adding users...
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- User Details Dialog -->
    <UserDetailsDialog
      :user-id="selectedUserId"
      v-model:open="isUserDialogOpen"
    />
  </div>
</template>
