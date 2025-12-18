import type { Task as KanbanTask } from '@/types/kanban'

import type { Project } from '../data/schema'

// Map project statuses to kanban column IDs
const statusToColumnId: Record<string, string> = {
  'active': 'in-progress',
  'on-hold': 'backlog',
  'completed': 'done',
  'cancelled': 'canceled',
}

// Map kanban column IDs back to project statuses
const columnIdToStatus: Record<string, string> = {
  'in-progress': 'active',
  'backlog': 'on-hold',
  'done': 'completed',
  'canceled': 'cancelled',
}

/**
 * Convert a Project to Kanban Task format
 */
export function projectToKanbanTask(project: Project): KanbanTask {
  return {
    id: project.id.toString(),
    title: project.name,
    description: project.description || undefined,
    status: statusToColumnId[project.status.toLowerCase()] || project.status.toLowerCase(),
    labels: project.category ? [project.category] : [],
    dueDate: project.end_date || project.endDate || undefined,
    createdAt: project.created_at || new Date().toISOString(),
  }
}

/**
 * Convert a Kanban Task back to Project format
 */
export function kanbanTaskToProject(task: KanbanTask, originalProject?: Project): Partial<Project> {
  const baseProject: Partial<Project> = {
    name: task.title,
    description: task.description || null,
    status: columnIdToStatus[task.status || ''] || task.status || 'active',
    category: task.labels?.[0] || originalProject?.category || 'other',
    end_date: task.dueDate
      ? typeof task.dueDate === 'string'
        ? task.dueDate
        : new Date(task.dueDate).toISOString().split('T')[0]
      : null,
    endDate: task.dueDate
      ? typeof task.dueDate === 'string'
        ? task.dueDate
        : new Date(task.dueDate).toISOString().split('T')[0]
      : null,
  }

  // Preserve original project data if provided
  if (originalProject) {
    return {
      ...originalProject,
      ...baseProject,
    }
  }

  return baseProject
}

/**
 * Convert an array of Projects to Kanban Tasks
 */
export function projectsToKanbanTasks(projects: Project[]): KanbanTask[] {
  return projects.map(projectToKanbanTask)
}

/**
 * Convert an array of Kanban Tasks to Projects
 */
export function kanbanTasksToProjects(tasks: KanbanTask[], originalProjects: Project[]): Project[] {
  return tasks.map((task) => {
    const originalProject = originalProjects.find(p => p.id.toString() === task.id)
    const partialProject = kanbanTaskToProject(task, originalProject)
    return {
      ...originalProject!,
      ...partialProject,
    } as Project
  })
}
