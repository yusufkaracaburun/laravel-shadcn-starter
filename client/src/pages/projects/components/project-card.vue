<script setup lang="ts">
import { Calendar, FolderKanban, MoreVertical } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import type { Project } from '@/services/projects.service'

import { Badge } from '@/components/ui/badge'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Progress } from '@/components/ui/progress'
import { StatusBadge } from '@/components/ui/status-badge'

import { categories, statuses } from '../data/data'
import ProjectDelete from './project-delete.vue'
import ProjectResourceDialog from './project-resource-dialog.vue'

interface Props {
  project: Project
}

const props = defineProps<Props>()
const router = useRouter()

const showComponent = shallowRef<
  typeof ProjectResourceDialog | typeof ProjectDelete | null
>(null)
const isDialogOpen = ref(false)

type TCommand = 'view' | 'edit' | 'delete'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({
        name: '/projects/[id]',
        params: { id: props.project.id.toString() },
      })
      break
    case 'edit':
      showComponent.value = ProjectResourceDialog
      isDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = ProjectDelete
      isDialogOpen.value = true
      break
  }
}

function formatDate(dateString: string | null): string {
  if (!dateString) return '—'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const status = computed(() =>
  statuses.find((s) => s.value === props.project.status),
)
const category = computed(() =>
  categories.find((c) => c.value === props.project.category),
)
</script>

<template>
  <Card
    class="hover:shadow-md transition-shadow cursor-pointer"
    @click="
      router.push({
        name: '/projects/[id]',
        params: { id: project.id.toString() },
      })
    "
  >
    <CardHeader>
      <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
          <CardTitle class="text-lg mb-1 line-clamp-1">
            {{ project.name }}
          </CardTitle>
          <CardDescription class="line-clamp-2 mt-1">
            {{ project.description || 'No description' }}
          </CardDescription>
        </div>
        <DropdownMenu @click.stop>
          <DropdownMenuTrigger as-child @click.stop>
            <UiButton
              variant="ghost"
              size="icon"
              class="size-8 shrink-0"
              @click.stop
            >
              <MoreVertical class="size-4" />
              <span class="sr-only">Open menu</span>
            </UiButton>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            <DropdownMenuItem @click.stop="handleSelect('view')">
              View
            </DropdownMenuItem>
            <UiDialogTrigger as-child>
              <DropdownMenuItem @click.stop="handleSelect('edit')">
                Edit
              </DropdownMenuItem>
            </UiDialogTrigger>
            <UiDialogTrigger as-child>
              <DropdownMenuItem
                class="text-destructive"
                @click.stop="handleSelect('delete')"
              >
                Delete
              </DropdownMenuItem>
            </UiDialogTrigger>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </CardHeader>
    <CardContent class="space-y-4">
      <div class="flex items-center gap-2 flex-wrap">
        <StatusBadge
          v-if="status"
          :status="status.value"
          type="project"
          :icon="status.icon"
          :label="status.label"
        />
        <Badge v-if="category" variant="outline">
          {{ category.label }}
        </Badge>
      </div>

      <div class="space-y-2">
        <div class="flex items-center justify-between text-sm">
          <span class="text-muted-foreground">Progress</span>
          <span class="font-medium">{{ project.progress }}%</span>
        </div>
        <Progress :model-value="project.progress" />
      </div>

      <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <div class="flex items-center gap-1 text-muted-foreground mb-1">
            <Calendar class="size-3" />
            <span>Start</span>
          </div>
          <div class="font-medium">
            {{ formatDate(project.start_date) }}
          </div>
        </div>
        <div>
          <div class="flex items-center gap-1 text-muted-foreground mb-1">
            <Calendar class="size-3" />
            <span>End</span>
          </div>
          <div class="font-medium">
            {{ formatDate(project.end_date) }}
          </div>
        </div>
      </div>
    </CardContent>
    <CardFooter class="pt-0">
      <div class="flex items-center gap-2 text-xs text-muted-foreground w-full">
        <FolderKanban class="size-3" />
        <span>Project #{{ project.id }}</span>
      </div>
    </CardFooter>
  </Card>

  <UiDialog v-model:open="isDialogOpen">
    <UiDialogContent>
      <component
        :is="showComponent"
        v-if="showComponent"
        :project="project"
        @close="isDialogOpen = false"
      />
    </UiDialogContent>
  </UiDialog>
</template>
