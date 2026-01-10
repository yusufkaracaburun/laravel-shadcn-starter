<script setup lang="ts">
import type { DateValue } from '@internationalized/date'

import {
  CalendarDateTime,
  DateFormatter,
  getLocalTimeZone,
  parseAbsoluteToLocal,
} from '@internationalized/date'
import { useTimeAgo } from '@vueuse/core'
import {
  Calendar as CalendarIcon,
  ChevronDown,
  ChevronUp,
  Clock,
  Copy,
  Edit2,
  EllipsisVertical,
  Equal,
  Folder,
  GripVertical,
  Link,
  MessageSquare,
  Plus,
  Trash2,
} from 'lucide-vue-next'
import Draggable from 'vuedraggable'

import type { Column, NewTask, Task } from '@/types/kanban'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import {
  Card,
  CardAction,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import {
  Tooltip,
  TooltipContent,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { useKanban } from '@/pages/tasks/composables/use-kanban.composable'
import { cn } from '@/lib/utils'
// Import TaskResourceDialog for tasks page integration
import TaskResourceDialogWrapper from '@/pages/tasks/components/task-resource-dialog.vue'
import { getPriorityColor } from '@/utils/status-colors'

interface Props {
  useTaskForm?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  useTaskForm: false,
})

const emit = defineEmits<{
  'task-updated': [task: Task, columnId: string]
  'task-created': [task: Task, columnId: string]
}>()

const {
  board,
  addTask,
  updateTask,
  removeTask,
  setColumns,
  removeColumn,
  updateColumn,
} = useKanban()

// Generate task ID function (same as in useKanban)
function generateTaskId(): string {
  const key = 'kanban.task.counter'
  let counter = 1

  if (typeof window !== 'undefined') {
    const saved = localStorage.getItem(key)
    counter = saved ? Number.parseInt(saved) + 1 : 1
    localStorage.setItem(key, counter.toString())
  }

  return `TASK-${counter.toString().padStart(3, '0')}`
}

// For TaskResourceDialog integration (only used when useTaskForm is true)
const selectedTableTask = ref<any>(null)
const isEditDialogOpen = ref(false)

const df = new DateFormatter('en-US', {
  dateStyle: 'medium',
})
const dueDate = ref<DateValue | undefined>()
const dueTime = ref<string | undefined>('00:00')

watch(
  () => dueTime.value,
  (newVal) => {
    if (!newVal)
      return
    if (dueDate.value) {
      const [hours, minutes] = newVal.split(':').map(Number)
      dueDate.value = new CalendarDateTime(
        dueDate.value.year,
        dueDate.value.month,
        dueDate.value.day,
        hours,
        minutes,
      )
    }
  },
)

const showModalTask = ref<{
  type: 'create' | 'edit'
  open: boolean
  columnId: string | null
  taskId?: string | null
}>({
  type: 'create',
  open: false,
  columnId: null,
  taskId: null,
})
const newTask = reactive<NewTask>({
  title: '',
  description: '',
  priority: undefined,
  dueDate: undefined,
  status: '',
  labels: undefined,
})
function resetData() {
  dueDate.value = undefined
  dueTime.value = '00:00'
}
watch(
  () => showModalTask.value.open,
  (newVal) => {
    if (!newVal)
      resetData()
  },
)

function openNewTask(colId: string) {
  // If using TaskForm, open TaskResourceDialog instead
  if (props.useTaskForm) {
    // Create a new task object with the column status
    const statusMap: Record<string, string> = {
      'in-progress': 'in progress',
      'backlog': 'backlog',
      'todo': 'todo',
      'done': 'done',
      'canceled': 'canceled',
    }
    selectedTableTask.value = {
      id: '', // Will be generated
      title: '',
      description: '',
      status: statusMap[colId] || colId,
      labels: [],
      priority: 'medium',
      dueDate: null,
      createdAt: new Date().toISOString(),
    }
    isEditDialogOpen.value = true
    return
  }

  // Original kanban form logic
  showModalTask.value = { type: 'create', open: true, columnId: colId }
  newTask.title = ''
  newTask.description = ''
  newTask.priority = undefined
}
function createTask() {
  if (!showModalTask.value.columnId || !newTask.title.trim())
    return
  const payload: NewTask = {
    title: newTask.title.trim(),
    description: newTask.description?.trim(),
    priority: newTask.priority,
    dueDate: dueDate.value?.toDate(getLocalTimeZone()),
    status: showModalTask.value.columnId,
    labels: newTask.labels,
  }
  addTask(showModalTask.value.columnId, payload)
  showModalTask.value.open = false
}

function editTask() {
  if (!showModalTask.value.columnId || !newTask.title.trim())
    return
  const payload: Partial<Task> = {
    title: newTask.title.trim(),
    description: newTask.description?.trim(),
    priority: newTask.priority,
    dueDate: dueDate.value?.toDate(getLocalTimeZone()),
    status: showModalTask.value.columnId,
    labels: newTask.labels,
  }
  updateTask(showModalTask.value.columnId, showModalTask.value.taskId!, payload)
  showModalTask.value.open = false
}

function showEditTask(colId: string, taskId: string) {
  const task = board.value.columns
    .find(c => c.id === colId)
    ?.tasks
    .find(t => t.id === taskId)
  if (!task)
    return

  // If using TaskForm, convert to table task format
  if (props.useTaskForm) {
    // Convert kanban task to table task format
    const tableTask = {
      id: task.id,
      title: task.title,
      description: task.description || '',
      status: colId === 'in-progress' ? 'in progress' : colId,
      labels: task.labels || [],
      priority: task.priority || 'medium',
      dueDate: task.dueDate,
      createdAt: task.createdAt,
    }
    selectedTableTask.value = tableTask
    isEditDialogOpen.value = true
    return
  }

  // Original kanban form logic
  newTask.title = task.title
  newTask.description = task.description
  newTask.priority = task.priority
  if (typeof task.dueDate === 'object') {
    task.dueDate = task.dueDate.toISOString()
  }
  dueDate.value = parseAbsoluteToLocal(task.dueDate as string)
  dueTime.value = `${dueDate.value.hour < 10 ? `0${dueDate.value?.hour}` : dueDate.value?.hour}:${dueDate.value.minute < 10 ? `0${dueDate.value?.minute}` : dueDate.value?.minute}`
  newTask.status = task.status
  newTask.labels = task.labels
  showModalTask.value = { type: 'edit', open: true, columnId: colId, taskId }
}

function handleTaskFormUpdate(updatedTask: any) {
  // Map status to column ID
  const statusToColId: Record<string, string> = {
    'backlog': 'backlog',
    'todo': 'todo',
    'in progress': 'in-progress',
    'done': 'done',
    'canceled': 'canceled',
  }

  const newColId = statusToColId[updatedTask.status] || updatedTask.status
  const taskId = updatedTask.id

  // Check if this is a new task (no ID yet or empty string)
  const isNewTask =
    !taskId
    || taskId === ''
    || (selectedTableTask.value
      && (!selectedTableTask.value.id || selectedTableTask.value.id === ''))

  if (isNewTask) {
    // Create new task
    const kanbanTask: Task = {
      id: generateTaskId(),
      title: updatedTask.title,
      description: updatedTask.description || '',
      priority: updatedTask.priority as 'low' | 'medium' | 'high' | undefined,
      dueDate: updatedTask.dueDate,
      labels: updatedTask.labels || [],
      status: newColId,
      createdAt: updatedTask.createdAt || new Date(),
    }

    // Add to the appropriate column
    let col = board.value.columns.find(c => c.id === newColId)

    // If column doesn't exist, create it
    if (!col) {
      const columnTitles: Record<string, string> = {
        'backlog': 'Backlog',
        'todo': 'Todo',
        'in-progress': 'In Progress',
        'done': 'Done',
        'canceled': 'Canceled',
      }
      col = {
        id: newColId,
        title: columnTitles[newColId] || newColId,
        tasks: [],
      }
      board.value.columns.push(col)
    }

    // Add task to column
    col.tasks.unshift(kanbanTask)
    setColumns([...board.value.columns])
    emit('task-created', kanbanTask, newColId)
  } else {
    // For updates, we need selectedTableTask
    if (!selectedTableTask.value)
      return
    // Update existing task
    // Find current column and task
    let currentCol: Column | undefined
    let currentTask: Task | undefined

    for (const col of board.value.columns) {
      const found = col.tasks.find(t => t.id === taskId)
      if (found) {
        currentCol = col
        currentTask = found
        break
      }
    }

    if (!currentTask)
      return

    // Prepare update payload
    const payload: Partial<Task> = {
      title: updatedTask.title,
      description: updatedTask.description,
      priority: updatedTask.priority as 'low' | 'medium' | 'high' | undefined,
      dueDate: updatedTask.dueDate,
      labels: updatedTask.labels || [],
    }

    // If status changed, move to new column
    if (currentCol && currentCol.id !== newColId) {
      // Remove from old column
      currentCol.tasks = currentCol.tasks.filter(t => t.id !== taskId)

      // Add to new column
      const newCol = board.value.columns.find(c => c.id === newColId)
      if (newCol) {
        newCol.tasks.unshift({ ...currentTask, ...payload, status: newColId })
      }
    } else {
      // Update in place
      Object.assign(currentTask, payload)
    }

    setColumns([...board.value.columns])
    emit('task-updated', currentTask, newColId)
  }

  isEditDialogOpen.value = false
  selectedTableTask.value = null
}

function onColumnDrop() {
  // Full columns re-ordered
  setColumns([...board.value.columns])
}

function renameColumn(id: string) {
  const titleRef = document.getElementById(`col-title-${id}`) as HTMLElement
  if (titleRef)
    setTimeout(() => titleRef.focus(), 500)
}

function onUpdateColumn(evt: Event, id: string) {
  const target = evt.target as HTMLElement
  if (!target.textContent?.trim())
    return
  updateColumn(id, target.textContent.trim())
}

function onTaskDrop() {
  // ensure state is persisted after any move (within or across columns)
  nextTick(() => setColumns([...board.value.columns]))
}

function colorPriority(p?: Task['priority']) {
  return getPriorityColor(p)
}

function iconPriority(p?: Task['priority']) {
  if (!p)
    return Equal
  if (p === 'low')
    return ChevronDown
  if (p === 'medium')
    return Equal
  return ChevronUp
}

function getLabelVariant(
  label: string,
): 'default' | 'secondary' | 'destructive' | 'outline' {
  const normalizedLabel = label.toLowerCase()
  if (normalizedLabel === 'bug')
    return 'destructive'
  if (normalizedLabel === 'feature')
    return 'default'
  if (normalizedLabel === 'documentation')
    return 'secondary'
  return 'outline'
}

const timeAgoOptions = {
  updateInterval: 1000,
}
</script>

<template>
  <div class="flex gap-4 overflow-x-auto overflow-y-hidden pb-4">
    <!-- Columns Draggable wrapper -->
    <Draggable
      v-model="board.columns"
      class="flex gap-4 min-w-max"
      item-key="id"
      :animation="180"
      handle=".col-handle"
      ghost-class="opacity-50"
      @end="onColumnDrop"
    >
      <template #item="{ element: col }: { element: Column }">
        <Card class="w-[272px] shrink-0 py-2 gap-4 self-start">
          <CardHeader
            class="flex flex-row items-center justify-between gap-2 px-2"
          >
            <CardTitle class="font-semibold text-sm flex items-center gap-2">
              <GripVertical class="col-handle cursor-grab opacity-60 size-4" />
              <span
                :id="`col-title-${col.id}`"
                contenteditable="true"
                class="hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 px-1 rounded"
                @blur="onUpdateColumn($event, col.id)"
                @keydown.enter.prevent
              >{{ col.title }}</span>
              <Badge
                variant="secondary"
                class="h-5 min-w-5 px-1 font-mono tabular-nums"
              >
                {{ col.tasks.length }}
              </Badge>
            </CardTitle>
            <CardAction class="flex">
              <Button
                size="icon-sm"
                variant="ghost"
                class="size-7 text-muted-foreground"
                @click="openNewTask(col.id)"
              >
                <Plus class="size-4" />
              </Button>
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button
                    size="icon-sm"
                    variant="ghost"
                    class="size-7 text-muted-foreground"
                  >
                    <EllipsisVertical class="size-4" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="w-20" align="start">
                  <DropdownMenuItem @click="renameColumn(col.id)">
                    <Edit2 class="size-4" />
                    Rename
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem
                    variant="destructive"
                    class="text-destructive"
                    @click="removeColumn(col.id)"
                  >
                    <Trash2 class="size-4" />
                    Delete
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </CardAction>
          </CardHeader>
          <CardContent class="px-2 overflow-y-auto overflow-x-hidden flex-1">
            <!-- Tasks within the column -->
            <Draggable
              v-model="col.tasks"
              :group="{ name: 'kanban-tasks', pull: true, put: true }"
              item-key="id"
              :animation="180"
              class="flex flex-col gap-3 min-h-[24px] p-0.5"
              ghost-class="opacity-50"
              @end="onTaskDrop"
            >
              <template #item="{ element: t }: { element: Task }">
                <div
                  class="rounded-xl border bg-card px-3 py-2 shadow-sm hover:bg-accent/50 cursor-pointer"
                  @click="showEditTask(col.id, t.id)"
                >
                  <div class="flex items-start justify-between gap-2">
                    <div class="text-[10px] text-muted-foreground">
                      {{ t.id }}
                    </div>
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button
                          size="icon-sm"
                          variant="ghost"
                          class="size-7 text-muted-foreground"
                          title="More actions"
                          @click.stop
                        >
                          <EllipsisVertical class="size-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent class="w-20" align="start">
                        <DropdownMenuItem @click="showEditTask(col.id, t.id)">
                          <Edit2 class="size-4" />
                          Edit
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem>
                          <Copy class="size-4" />
                          Copy card
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                          <Link class="size-4" />
                          Copy link
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                          variant="destructive"
                          class="text-destructive"
                          @click="removeTask(col.id, t.id)"
                        >
                          <Trash2 class="size-4" />
                          Delete
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </div>
                  <p class="text-sm font-medium leading-5 mt-1">
                    {{ t.title }}
                  </p>
                  <div
                    v-if="t.labels && t.labels.length > 0"
                    class="mt-2 flex items-center gap-1"
                  >
                    <Badge
                      v-for="label in t.labels"
                      :key="label"
                      :variant="getLabelVariant(label)"
                      class="text-[10px] h-4 px-1 leading-tight"
                    >
                      {{ label.charAt(0).toUpperCase() + label.slice(1) }}
                    </Badge>
                  </div>
                  <div class="mt-3 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                      <div
                        class="flex items-center text-[10px] text-muted-foreground gap-1"
                      >
                        <Folder class="size-3" />
                        <span>4</span>
                      </div>
                      <div
                        class="flex items-center text-[10px] text-muted-foreground gap-1"
                      >
                        <MessageSquare class="size-3" />
                        <span>2</span>
                      </div>
                      <div
                        v-if="t.dueDate"
                        class="flex items-center text-[10px] text-muted-foreground gap-1"
                      >
                        <Clock class="size-3" />
                        <span>{{ useTimeAgo(t.dueDate, timeAgoOptions) }}</span>
                      </div>
                    </div>
                    <div class="flex items-center gap-2">
                      <Tooltip v-if="t.priority">
                        <TooltipTrigger as-child>
                          <component
                            :is="iconPriority(t.priority)"
                            class="size-4"
                            :class="colorPriority(t.priority)"
                          />
                        </TooltipTrigger>
                        <TooltipContent class="capitalize">
                          {{ t.priority }}
                        </TooltipContent>
                      </Tooltip>
                      <Avatar class="size-6">
                        <AvatarImage
                          src="/avatars/avatartion.png"
                          alt="avatar"
                        />
                        <AvatarFallback class="text-[10px]">
                          DP
                        </AvatarFallback>
                      </Avatar>
                    </div>
                  </div>
                </div>
              </template>
            </Draggable>
          </CardContent>
          <CardFooter class="px-2 mt-auto">
            <Button
              size="sm"
              variant="ghost"
              class="text-muted-foreground"
              @click="openNewTask(col.id)"
            >
              <Plus class="size-4" />
              Add Task
            </Button>
          </CardFooter>
        </Card>
      </template>
    </Draggable>
  </div>

  <!-- New Task Dialog -->
  <Dialog v-model:open="showModalTask.open">
    <DialogContent class="sm:max-w-[520px]">
      <DialogHeader>
        <DialogTitle>
          {{ showModalTask.type === 'create' ? 'New Task' : 'Edit Task' }}
        </DialogTitle>
        <DialogDescription class="sr-only">
          {{
            showModalTask.type === 'create'
              ? 'Add a new task to the board'
              : 'Edit the task'
          }}
        </DialogDescription>
      </DialogHeader>
      <div class="flex flex-col gap-3">
        <div
          class="grid items-baseline grid-cols-1 md:grid-cols-4 md:[&>label]:col-span-1 *:col-span-3 gap-3"
        >
          <Label>Title</Label>
          <Input v-model="newTask.title" placeholder="Title" />
          <Label>Description</Label>
          <Textarea
            v-model="newTask.description"
            placeholder="Description (optional)"
            rows="4"
          />
          <Label>Priority</Label>
          <Select v-model="newTask.priority">
            <SelectTrigger class="w-full">
              <SelectValue placeholder="Select a priority" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="low">
                Low
              </SelectItem>
              <SelectItem value="medium">
                Medium
              </SelectItem>
              <SelectItem value="high">
                High
              </SelectItem>
            </SelectContent>
          </Select>
          <Label>Due Date</Label>
          <div class="flex items-center gap-1">
            <Popover>
              <PopoverTrigger as-child>
                <Button
                  variant="outline"
                  :class="
                    cn(
                      'flex-1 justify-start text-left font-normal px-3',
                      !dueDate && 'text-muted-foreground',
                    )
                  "
                >
                  <CalendarIcon class="mr-2 size-4" />
                  {{
                    dueDate
                      ? df.format(dueDate.toDate(getLocalTimeZone()))
                      : 'Pick a date'
                  }}
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-auto p-0">
                <Calendar v-model="dueDate" initial-focus />
              </PopoverContent>
            </Popover>
            <Input
              id="time-picker"
              v-model="dueTime"
              type="time"
              step="60"
              default-value="00:00"
              class="flex-1 bg-background appearance-none [&::-webkit-calendar-picker-indicator]:hidden [&::-webkit-calendar-picker-indicator]:appearance-none"
            />
          </div>
        </div>
      </div>
      <DialogFooter>
        <Button variant="secondary" @click="showModalTask.open = false">
          Cancel
        </Button>
        <Button
          @click="showModalTask.type === 'create' ? createTask() : editTask()"
        >
          {{ showModalTask.type === 'create' ? 'Create' : 'Update' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <!-- TaskResourceDialog for tasks page integration -->
  <Dialog v-if="useTaskForm" v-model:open="isEditDialogOpen">
    <DialogContent>
      <TaskResourceDialogWrapper
        :task="selectedTableTask"
        @close="
          () => {
            isEditDialogOpen = false
            selectedTableTask = null
          }
        "
        @task-updated="handleTaskFormUpdate"
        @task-created="handleTaskFormUpdate"
      />
    </DialogContent>
  </Dialog>
</template>
