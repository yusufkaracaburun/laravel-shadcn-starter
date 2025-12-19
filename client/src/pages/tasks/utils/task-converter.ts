import type { Task as KanbanTask } from '@/types/kanban'

import type { Task as TableTask } from '../data/schema'

// Map task statuses to kanban column IDs
const statusToColumnId: Record<string, string> = {
  'backlog': 'backlog',
  'todo': 'todo',
  'in progress': 'in-progress',
  'done': 'done',
  'canceled': 'canceled',
}

// Map kanban column IDs back to task statuses
const columnIdToStatus: Record<string, string> = {
  'backlog': 'backlog',
  'todo': 'todo',
  'in-progress': 'in progress',
  'done': 'done',
  'canceled': 'canceled',
}

/**
 * Convert a Table Task to Kanban Task format
 */
export function tableTaskToKanbanTask(task: TableTask): KanbanTask {
  return {
    id: task.id,
    title: task.title,
    description: task.description,
    priority: task.priority as 'low' | 'medium' | 'high' | undefined,
    status:
      statusToColumnId[task.status.toLowerCase()] || task.status.toLowerCase(),
    labels: task.labels || [],
    dueDate: task.dueDate,
    createdAt: task.createdAt || new Date().toISOString(),
  }
}

/**
 * Convert a Kanban Task back to Table Task format
 */
export function kanbanTaskToTableTask(task: KanbanTask): TableTask {
  return {
    id: task.id,
    title: task.title,
    description: task.description,
    status: columnIdToStatus[task.status || ''] || task.status || 'todo',
    labels: task.labels || [],
    priority: task.priority || 'medium',
    dueDate: task.dueDate,
    createdAt: task.createdAt || new Date().toISOString(),
  }
}

/**
 * Convert an array of Table Tasks to Kanban Tasks
 */
export function tableTasksToKanbanTasks(tasks: TableTask[]): KanbanTask[] {
  return tasks.map(tableTaskToKanbanTask)
}

/**
 * Convert an array of Kanban Tasks to Table Tasks
 */
export function kanbanTasksToTableTasks(tasks: KanbanTask[]): TableTask[] {
  return tasks.map(kanbanTaskToTableTask)
}
