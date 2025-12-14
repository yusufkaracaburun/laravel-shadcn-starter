<script setup lang="ts">
import { nextTick, watch } from 'vue'

import type { Column, Task as KanbanTask } from '@/types/kanban'

import KanbanBoard from '@/components/kanban/kanban-board.vue'
import { useKanban } from '@/composables/use-kanban'
import { useProjects } from '@/composables/use-projects'

import type { Project } from '../data/schema'

import { kanbanTaskToProject, projectsToKanbanTasks } from '../utils/project-converter'

interface Props {
  projects: Project[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:projects': [projects: Project[]]
}>()

const { board, setColumns } = useKanban()
const { updateProject } = useProjects()

// Default columns based on project statuses
const defaultColumns: Column[] = [
  { id: 'backlog', title: 'On Hold', tasks: [] },
  { id: 'in-progress', title: 'Active', tasks: [] },
  { id: 'done', title: 'Completed', tasks: [] },
  { id: 'canceled', title: 'Cancelled', tasks: [] },
]

// Initialize board state immediately with projects data (before useKanban's onMounted loads from localStorage)
const initialKanbanTasks = projectsToKanbanTasks(props.projects)
const initialColumns = defaultColumns.map(col => ({ ...col, tasks: [] as KanbanTask[] }))

initialKanbanTasks.forEach((task) => {
  const columnId = task.status || 'in-progress'
  const column = initialColumns.find(c => c.id === columnId)
  if (column) {
    column.tasks.push(task)
  }
})

const visibleInitialColumns = initialColumns.filter(col =>
  col.tasks.length > 0 || ['in-progress', 'done'].includes(col.id),
)

// Set board state immediately to override any localStorage data
board.value.columns = visibleInitialColumns

// Initialize previous state map
const previousColumnState = ref<Map<string, string>>(new Map())
initialKanbanTasks.forEach((task) => {
  const columnId = task.status || 'in-progress'
  previousColumnState.value.set(task.id, columnId)
})

// Track if we're syncing to avoid circular updates
const isSyncing = ref(false)

// Organize projects into columns by status
function organizeProjectsIntoColumns() {
  if (isSyncing.value)
    return

  isSyncing.value = true
  const columns = defaultColumns.map(col => ({ ...col, tasks: [] as KanbanTask[] }))
  const kanbanTasks = projectsToKanbanTasks(props.projects)

  kanbanTasks.forEach((task) => {
    const columnId = task.status || 'in-progress'
    const column = columns.find(c => c.id === columnId)
    if (column) {
      column.tasks.push(task)
    }
  })

  // Show default columns (in-progress, done) even if empty, and columns that have projects
  const visibleColumns = columns.filter(col =>
    col.tasks.length > 0 || ['in-progress', 'done'].includes(col.id),
  )

  // Update the kanban board with organized projects
  // Use setColumns to ensure persistence is handled correctly
  board.value.columns = visibleColumns
  setColumns(visibleColumns)

  // Update previous state map to match current organization
  previousColumnState.value.clear()
  kanbanTasks.forEach((task) => {
    const columnId = task.status || 'in-progress'
    previousColumnState.value.set(task.id, columnId)
  })

  nextTick(() => {
    isSyncing.value = false
  })
}

// Watch for changes in projects and reorganize (only when not syncing)
watch(() => props.projects, () => {
  if (!isSyncing.value) {
    organizeProjectsIntoColumns()
  }
}, { deep: true })

// Watch for changes in kanban board and sync back to projects
watch(() => board.value.columns, (newColumns) => {
  if (isSyncing.value)
    return

  isSyncing.value = true

  // Build current state map: projectId -> columnId
  const currentState = new Map<string, string>()
  newColumns.forEach((col) => {
    col.tasks.forEach((task) => {
      currentState.set(task.id, col.id)
    })
  })

  // Find projects that changed columns
  const updatePromises: Promise<any>[] = []
  currentState.forEach((newColumnId, projectId) => {
    const oldColumnId = previousColumnState.value.get(projectId)
    
    // Only update if column actually changed
    if (oldColumnId !== newColumnId) {
      const originalProject = props.projects.find(p => p.id.toString() === projectId)
      if (originalProject) {
        const updatedProject = kanbanTaskToProject(
          { id: projectId, status: newColumnId } as KanbanTask,
          originalProject,
        )
        
        // Update project status via API (Vue Query will auto-refetch)
        updatePromises.push(
          updateProject(originalProject.id, {
            status: updatedProject.status || originalProject.status,
          }).catch((error) => {
            console.error('Failed to update project:', error)
          }),
        )
      }
    }
  })

  // Update previous state
  previousColumnState.value = currentState

  // Vue Query mutations already invalidate queries, so no need to manually refetch
  nextTick(() => {
    isSyncing.value = false
  })
}, { deep: true })

function handleProjectUpdated(task: KanbanTask, _columnId: string) {
  // Convert kanban task to project and update via API
  const originalProject = props.projects.find(p => p.id.toString() === task.id)
  if (!originalProject) {
    return
  }

  const updatedProject = kanbanTaskToProject(task, originalProject)
  
  // Update project via API (Vue Query will auto-refetch)
  updateProject(originalProject.id, {
    status: updatedProject.status || originalProject.status,
    name: updatedProject.name || originalProject.name,
    description: updatedProject.description,
    category: updatedProject.category || originalProject.category,
    end_date: updatedProject.end_date,
  }).catch((error) => {
    console.error('Failed to update project:', error)
  })
}

// Track if we've forced initialization
const hasForcedInit = ref(false)

// Force organize projects immediately when component is created
organizeProjectsIntoColumns()
hasForcedInit.value = true

// Watch for changes in projects prop (when switching views or projects are updated)
watch(() => props.projects, () => {
  if (!isSyncing.value && props.projects.length > 0) {
    organizeProjectsIntoColumns()
  }
}, { deep: true, immediate: true })

// Watch for when useKanban loads from localStorage and override
watch(() => board.value.columns, (newColumns) => {
  // If useKanban just loaded from localStorage (columns exist but don't match our projects)
  if (!hasForcedInit.value && newColumns.length > 0) {
    const hasOurProjects = props.projects.some((project) => {
      const kanbanTask = projectsToKanbanTasks([project])[0]
      return newColumns.some(col => col.tasks.some(t => t.id === kanbanTask.id))
    })

    // If localStorage data doesn't contain our projects, override it
    if (!hasOurProjects && props.projects.length > 0) {
      nextTick(() => {
        organizeProjectsIntoColumns()
        hasForcedInit.value = true
      })
    }
  }
}, { immediate: true })
</script>

<template>
  <div class="h-full">
    <KanbanBoard
      :use-task-form="false"
      @task-updated="handleProjectUpdated"
      @task-created="handleProjectUpdated"
    />
  </div>
</template>
