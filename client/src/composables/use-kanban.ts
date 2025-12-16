import type { BoardState, Column, NewTask, Task } from '@/types/kanban'

const STORAGE_KEY = 'kanban.board.v1'
const TASK_KEY_ID = 'TASK'

function generateTaskId(): string {
  const key = 'kanban.task.counter'
  let counter = 1

  if (typeof window !== 'undefined') {
    const saved = localStorage.getItem(key)
    counter = saved ? Number.parseInt(saved) + 1 : 1
    localStorage.setItem(key, counter.toString())
  }

  return `${TASK_KEY_ID}-${counter.toString().padStart(3, '0')}`
}

function generateColumnId(title: string): string {
  return title.toLowerCase().replace(/\s+/g, '-')
}

const defaultBoard: BoardState = {
  columns: [
    { id: 'todo', title: 'To Do', tasks: [] },
    { id: 'in-progress', title: 'In Progress', tasks: [] },
    { id: 'done', title: 'Done', tasks: [] },
  ],
}

export function useKanban() {
  const board = ref<BoardState>(load())

  onMounted(() => {
    board.value = load()
  })

  function load(): BoardState {
    if (typeof window !== 'undefined') {
      const raw = localStorage.getItem(STORAGE_KEY)
      if (raw) {
        try {
          return JSON.parse(raw) as BoardState
        } catch {
          // Invalid JSON, return default
        }
      }
    }
    return defaultBoard
  }

  function persist() {
    if (typeof window !== 'undefined') {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(board.value))
    }
  }

  function addColumn(title: string) {
    const newCol: Column = { id: generateColumnId(title), title, tasks: [] }
    board.value.columns.push(newCol)
    persist()
  }

  function removeColumn(id: string) {
    board.value.columns = board.value.columns.filter((c) => c.id !== id)
    persist()
  }

  function updateColumn(id: string, title: string) {
    const col = board.value.columns.find((c) => c.id === id)
    if (!col) return
    col.title = title
    persist()
  }

  function addTask(columnId: string, payload: NewTask) {
    const col = board.value.columns.find((c) => c.id === columnId)
    if (!col) return
    col.tasks.unshift({ id: generateTaskId(), createdAt: new Date(), ...payload })
    persist()
  }

  function updateTask(columnId: string, taskId: string, patch: Partial<Task>) {
    const col = board.value.columns.find((c) => c.id === columnId)
    if (!col) return
    const t = col.tasks.find((t) => t.id === taskId)
    if (!t) return
    Object.assign(t, patch)
    persist()
  }

  function removeTask(columnId: string, taskId: string) {
    const col = board.value.columns.find((c) => c.id === columnId)
    if (!col) return
    col.tasks = col.tasks.filter((t) => t.id !== taskId)
    persist()
  }

  function setColumns(next: Column[]) {
    board.value.columns = next
    persist()
  }

  return {
    board,
    addColumn,
    removeColumn,
    updateColumn,
    addTask,
    updateTask,
    removeTask,
    setColumns,
    persist,
  }
}
