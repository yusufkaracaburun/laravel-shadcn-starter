<script setup lang="ts">
import type { ITeam } from '@/pages/teams/models/teams'
import type { IUser } from '@/pages/users/models/users'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  EllipsisIcon,
  PlusIcon,
  UsersIcon,
} from '@/composables/use-icons.composable'
import { useTeamService } from '@/pages/teams/services/teams.service'

interface Props {
  user: IUser
}

const props = defineProps<Props>()

// Team member interface - in real app, this would come from API
interface ITeamMember {
  id: number
  name: string
  email: string
  avatar: string | null
  status: 'active' | 'away' | 'offline'
  lastSeen: string
  role: 'admin' | 'editor' | 'viewer'
}

// Selected team state
const selectedTeamId = ref<number | null>(null)

// Team service for fetching team details with members
const teamService = useTeamService()

// Fetch team with members when selected
const selectedTeamIdRef = computed(() => selectedTeamId.value)
const teamMembersInclude = ref<string[]>(['members', 'users'])

const getTeamByIdQuery = teamService.getTeamByIdQuery(
  selectedTeamIdRef,
  teamMembersInclude,
)

const { data: teamByIdResponse, isLoading: isLoadingTeamMembers } =
  getTeamByIdQuery

// Mock team members data - in real app, this would come from teamByIdResponse
// This should be replaced with actual API data when available
const teamMembers = computed<ITeamMember[]>(() => {
  if (!selectedTeamId.value) {
    return []
  }

  // Mock data - replace with actual team members from API
  // const team = teamByIdResponse.value?.data
  // return team?.members || team?.users || []

  return [
    {
      id: 1,
      name: 'Alice Johnson',
      email: 'alice@example.com',
      avatar: null,
      status: 'active',
      lastSeen: '2 hours ago',
      role: 'admin',
    },
    {
      id: 2,
      name: 'Bob Smith',
      email: 'bob@example.com',
      avatar: null,
      status: 'away',
      lastSeen: '5 hours ago',
      role: 'editor',
    },
    {
      id: 3,
      name: 'Carol Williams',
      email: 'carol@example.com',
      avatar: null,
      status: 'offline',
      lastSeen: '2 days ago',
      role: 'viewer',
    },
  ]
})

// Get selected team
const selectedTeam = computed(() => {
  if (!selectedTeamId.value) {
    return null
  }
  return (
    props.user.teams?.find((team) => team.id === selectedTeamId.value) || null
  )
})

function selectTeam(team: ITeam) {
  selectedTeamId.value = team.id
}

function clearSelection() {
  selectedTeamId.value = null
}

function getInitials(name: string) {
  const parts = name.trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }
  return name[0].toUpperCase()
}

function getStatusVariant(status: string) {
  switch (status) {
    case 'active':
      return 'default'
    case 'away':
      return 'secondary'
    case 'offline':
      return 'outline'
    default:
      return 'secondary'
  }
}

function getStatusLabel(status: string) {
  switch (status) {
    case 'active':
      return 'Active'
    case 'away':
      return 'Away'
    case 'offline':
      return 'Offline'
    default:
      return status
  }
}
</script>

<template>
  <Card v-if="user.teams?.length">
    <CardHeader>
      <div class="flex items-center justify-between">
        <div>
          <CardTitle class="flex items-center gap-2">
            <UsersIcon class="size-5" />
            {{ selectedTeam ? 'Team Members' : 'Teams' }}
          </CardTitle>
          <CardDescription v-if="!selectedTeam">
            Teams this user belongs to
          </CardDescription>
          <CardDescription v-else>
            Members of {{ selectedTeam.name }}
          </CardDescription>
        </div>
        <div class="flex items-center gap-2">
          <Button
            v-if="selectedTeam"
            variant="outline"
            size="sm"
            @click="clearSelection"
          >
            ← Back
          </Button>
          <Button v-if="selectedTeam" size="sm">
            <PlusIcon class="mr-2 size-4" />
            Add Member
          </Button>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      <!-- Teams List View -->
      <div v-if="!selectedTeam" class="space-y-2">
        <div
          v-for="team in user.teams"
          :key="team.id"
          class="flex items-center justify-between p-3 rounded-lg border cursor-pointer hover:bg-muted/50 transition-colors"
          @click="selectTeam(team)"
        >
          <div>
            <div class="font-medium">
              {{ team.name }}
            </div>
            <div class="text-sm text-muted-foreground">
              {{ team.personal_team ? 'Personal Team' : 'Team' }}
            </div>
          </div>
          <Badge v-if="user.current_team_id === team.id" variant="default">
            Current
          </Badge>
        </div>
      </div>

      <!-- Team Members List View -->
      <div v-else class="space-y-0 divide-y">
        <div
          v-for="member in teamMembers"
          :key="member.id"
          class="flex items-center justify-between py-4 first:pt-0 last:pb-0"
        >
          <div class="flex items-center gap-4 flex-1 min-w-0">
            <!-- Avatar -->
            <Avatar class="size-10 shrink-0">
              <AvatarImage
                v-if="member.avatar"
                :src="member.avatar"
                :alt="member.name"
              />
              <AvatarFallback class="text-sm">
                {{ getInitials(member.name) }}
              </AvatarFallback>
            </Avatar>

            <!-- Name and Email -->
            <div class="flex-1 min-w-0">
              <div class="font-medium text-sm">
                {{ member.name }}
              </div>
              <div class="text-sm text-muted-foreground truncate">
                {{ member.email }}
              </div>
            </div>

            <!-- Status Badge -->
            <Badge :variant="getStatusVariant(member.status)" class="shrink-0">
              {{ getStatusLabel(member.status) }}
            </Badge>

            <!-- Last Seen -->
            <div class="text-sm text-muted-foreground shrink-0">
              {{ member.lastSeen }}
            </div>

            <!-- Role Badge -->
            <Badge variant="outline" class="shrink-0 capitalize">
              {{ member.role }}
            </Badge>
          </div>

          <!-- More Options -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="icon" class="size-8 shrink-0">
                <EllipsisIcon class="size-4" />
                <span class="sr-only">Open menu</span>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem>Edit</DropdownMenuItem>
              <DropdownMenuItem>Remove</DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>

        <!-- Empty State -->
        <div
          v-if="teamMembers.length === 0 && !isLoadingTeamMembers"
          class="py-8 text-center text-muted-foreground"
        >
          No team members found.
        </div>
      </div>
    </CardContent>
  </Card>
</template>
