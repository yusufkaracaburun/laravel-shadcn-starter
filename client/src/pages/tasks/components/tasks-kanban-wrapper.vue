<script setup lang="ts">
import { nextTick, onMounted, watch } from 'vue'

import type { Column, Task as KanbanTask } from '@/types/kanban'

import KanbanBoard from '@/components/kanban/kanban-board.vue'
import { useKanban } from '@/composables/use-kanban.composable'

import type { Task as TableTask } from '../data/schema'

import {
  kanbanTaskToTableTask,
  tableTasksToKanbanTasks,
} from '../utils/task-converter'

interface Props {
  tasks: TableTask[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:tasks': [tasks: TableTask[]]
}>()

const { board, setColumns } = useKanban()

// Default columns based on task statuses
const defaultColumns: Column[] = [
  { id: 'backlog', title: 'Backlog', tasks: [] },
  { id: 'todo', title: 'Todo', tasks: [] },
  { id: 'in-progress', title: 'In Progress', tasks: [] },
  { id: 'done', title: 'Done', tasks: [] },
  { id: 'canceled', title: 'Canceled', tasks: [] },
]

// Initialize board state immediately with tasks data (before useKanban's onMounted loads from localStorage)
const initialKanbanTasks = tableTasksToKanbanTasks(props.tasks)
const initialColumns = defaultColumns.map(col => ({
  ...col,
  tasks: [] as KanbanTask[],
}))

initialKanbanTasks.forEach((task) => {
  const columnId = task.status || 'todo'
  const column = initialColumns.find(c => c.id === columnId)
  if (column) {
    column.tasks.push(task)
  }
})

const visibleInitialColumns = initialColumns.filter(
  col =>
    col.tasks.length > 0 || ['todo', 'in-progress', 'done'].includes(col.id),
)

// Set board state immediately to override any localStorage data
board.value.columns = visibleInitialColumns

// Track if we're syncing to avoid circular updates
const isSyncing = ref(false)

// Organize tasks into columns by status
function organizeTasksIntoColumns() {
  if (isSyncing.value)
    return

  isSyncing.value = true
  const columns = defaultColumns.map(col => ({
    ...col,
    tasks: [] as KanbanTask[],
  }))
  const kanbanTasks = tableTasksToKanbanTasks(props.tasks)

  kanbanTasks.forEach((task) => {
    const columnId = task.status || 'todo'
    const column = columns.find(c => c.id === columnId)
    if (column) {
      column.tasks.push(task)
    }
  })

  // Show default columns (todo, in-progress, done) even if empty, and columns that have tasks
  const visibleColumns = columns.filter(
    col =>
      col.tasks.length > 0 || ['todo', 'in-progress', 'done'].includes(col.id),
  )

  // Update the kanban board with organized tasks
  // Use setColumns to ensure persistence is handled correctly
  board.value.columns = visibleColumns
  setColumns(visibleColumns)

  nextTick(() => {
    isSyncing.value = false
  })
}

// Watch for changes in tasks and reorganize (only when not syncing)
watch(
  () => props.tasks,
  () => {
    if (!isSyncing.value) {
      organizeTasksIntoColumns()
    }
  },
  { deep: true },
)

// Watch for changes in kanban board and sync back to tasks
watch(
  () => board.value.columns,
  () => {
    if (isSyncing.value)
      return

    isSyncing.value = true

    // Convert all kanban tasks back to table tasks
    const allKanbanTasks: KanbanTask[] = []
    board.value.columns.forEach((col) => {
      col.tasks.forEach((task) => {
        allKanbanTasks.push({ ...task, status: col.id })
      })
    })

    const updatedTasks = allKanbanTasks.map(kanbanTaskToTableTask)
    emit('update:tasks', updatedTasks)

    nextTick(() => {
      isSyncing.value = false
    })
  },
  { deep: true },
)

function handleTaskUpdated(task: KanbanTask, _columnId: string) {
  // Convert kanban task to table task and add/update in tasks array
  const tableTask = kanbanTaskToTableTask(task)

  // Check if task already exists
  const existingIndex = props.tasks.findIndex(t => t.id === tableTask.id)

  if (existingIndex >= 0) {
    // Update existing task
    const updatedTasks = [...props.tasks]
    updatedTasks[existingIndex] = tableTask
    emit('update:tasks', updatedTasks)
  } else {
    // Add new task
    const updatedTasks = [...props.tasks, tableTask]
    emit('update:tasks', updatedTasks)
  }

  // Trigger sync by reorganizing to ensure kanban board is in sync
  nextTick(() => {
    organizeTasksIntoColumns()
  })
}

// Track if we've forced initialization
const hasForcedInit = ref(false)

// Force organize tasks immediately when component is created
organizeTasksIntoColumns()
hasForcedInit.value = true

// Watch for changes in tasks prop (when switching views or tasks are updated)
watch(
  () => props.tasks,
  () => {
    if (!isSyncing.value && props.tasks.length > 0) {
      organizeTasksIntoColumns()
    }
  },
  { deep: true, immediate: true },
)

// Watch for when useKanban loads from localStorage and override
watch(
  () => board.value.columns,
  (newColumns) => {
    // If useKanban just loaded from localStorage (columns exist but don't match our tasks)
    if (!hasForcedInit.value && newColumns.length > 0) {
      const hasOurTasks = props.tasks.some((task) => {
        const kanbanTask = tableTasksToKanbanTasks([task])[0]
        return newColumns.some(col =>
          col.tasks.some(t => t.id === kanbanTask.id),
        )
      })

      // If localStorage data doesn't contain our tasks, override it
      if (!hasOurTasks && props.tasks.length > 0) {
        nextTick(() => {
          organizeTasksIntoColumns()
          hasForcedInit.value = true
        })
      }
    }
  },
  { immediate: true },
)

onMounted(() => {
  // Force organize after mount to ensure we override any localStorage data
  nextTick(() => {
    organizeTasksIntoColumns()
    hasForcedInit.value = true
  })
})
</script>

<template>
  <div class="h-full">
    <KanbanBoard
      :use-task-form="true"
      @task-updated="handleTaskUpdated"
      @task-created="handleTaskUpdated"
    />
  </div>
</template>
