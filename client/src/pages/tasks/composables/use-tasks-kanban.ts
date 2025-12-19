import type { BoardState, Column, NewTask, Task } from '@/types/kanban'

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
    { id: 'backlog', title: 'Backlog', tasks: [] },
    { id: 'todo', title: 'Todo', tasks: [] },
    { id: 'in-progress', title: 'In Progress', tasks: [] },
    { id: 'done', title: 'Done', tasks: [] },
    { id: 'canceled', title: 'Canceled', tasks: [] },
  ],
}

export function useTasksKanban(initialBoard?: BoardState) {
  const board = ref<BoardState>(initialBoard || defaultBoard)

  function addColumn(title: string) {
    const newCol: Column = { id: generateColumnId(title), title, tasks: [] }
    board.value.columns.push(newCol)
  }

  function removeColumn(id: string) {
    board.value.columns = board.value.columns.filter(c => c.id !== id)
  }

  function updateColumn(id: string, title: string) {
    const col = board.value.columns.find(c => c.id === id)
    if (!col)
      return
    col.title = title
  }

  function addTask(columnId: string, payload: NewTask) {
    const col = board.value.columns.find(c => c.id === columnId)
    if (!col)
      return
    col.tasks.unshift({
      id: generateTaskId(),
      createdAt: new Date(),
      ...payload,
    })
  }

  function updateTask(columnId: string, taskId: string, patch: Partial<Task>) {
    const col = board.value.columns.find(c => c.id === columnId)
    if (!col)
      return
    const t = col.tasks.find(t => t.id === taskId)
    if (!t)
      return
    Object.assign(t, patch)
  }

  function removeTask(columnId: string, taskId: string) {
    const col = board.value.columns.find(c => c.id === columnId)
    if (!col)
      return
    col.tasks = col.tasks.filter(t => t.id !== taskId)
  }

  function setColumns(next: Column[]) {
    board.value.columns = next
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
  }
}
