<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'

import { FormField } from '@/components/ui/form'
import { useProjects } from '@/composables/use-projects'

import type { Project } from '../data/schema'

import { categories, statuses } from '../data/data'

const props = defineProps<{
  project: Project | null
}>()
const emits = defineEmits(['close'])

const { createProject, updateProject } = useProjects()

const formSchema = toTypedSchema(
  z.object({
    name: z
      .string()
      .min(2)
      .max(100)
      .default(props.project?.name ?? ''),
    description: z
      .string()
      .nullable()
      .default(props.project?.description ?? null),
    status: z.string().default(props.project?.status ?? ''),
    category: z.string().default(props.project?.category ?? ''),
    startDate: z
      .string()
      .nullable()
      .default(props.project?.startDate ?? props.project?.start_date ?? null),
    endDate: z
      .string()
      .nullable()
      .default(props.project?.endDate ?? props.project?.end_date ?? null),
    progress: z
      .number()
      .min(0)
      .max(100)
      .default(props.project?.progress ?? 0),
  }),
)

const { isFieldDirty, handleSubmit, isSubmitting } = useForm({
  validationSchema: formSchema,
})

const onSubmit = handleSubmit(async (values) => {
  try {
    // Transform camelCase to snake_case for backend
    const backendData = {
      name: values.name,
      description: values.description || null,
      status: values.status,
      category: values.category,
      start_date: values.startDate || null,
      end_date: values.endDate || null,
      progress: values.progress ?? 0,
    }

    if (props.project?.id) {
      // Update existing project
      await updateProject(props.project.id, backendData)
    }
    else {
      // Create new project
      await createProject(backendData)
    }

    emits('close')
  }
  catch (error) {
    // Error handling is done in the composable
    // Just log for debugging
    console.error('Project form submission error:', error)
  }
})
</script>

<template>
  <form class="space-y-4" @submit="onSubmit">
    <FormField v-slot="{ componentField }" name="name" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Name</UiFormLabel>
        <UiFormControl>
          <UiInput type="text" placeholder="Project name" v-bind="componentField" />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="description" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Description</UiFormLabel>
        <UiFormControl>
          <UiTextarea placeholder="Project description" v-bind="componentField" />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="category" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Category</UiFormLabel>
        <UiFormControl>
          <UiSelect v-bind="componentField">
            <UiSelectTrigger>
              <UiSelectValue placeholder="Select a category" />
            </UiSelectTrigger>
            <UiSelectContent>
              <UiSelectGroup>
                <UiSelectItem
                  v-for="category in categories"
                  :key="category.value"
                  :value="category.value"
                >
                  {{ category.label }}
                </UiSelectItem>
              </UiSelectGroup>
            </UiSelectContent>
          </UiSelect>
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="status" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Status</UiFormLabel>
        <UiFormControl>
          <UiSelect v-bind="componentField">
            <UiSelectTrigger>
              <UiSelectValue placeholder="Select a status" />
            </UiSelectTrigger>
            <UiSelectContent>
              <UiSelectGroup>
                <UiSelectItem v-for="status in statuses" :key="status.value" :value="status.value">
                  <div class="flex items-center gap-2">
                    <component :is="status.icon" class="size-4 shrink-0" />
                    {{ status.label }}
                  </div>
                </UiSelectItem>
              </UiSelectGroup>
            </UiSelectContent>
          </UiSelect>
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="startDate" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Start Date</UiFormLabel>
        <UiFormControl>
          <UiInput type="date" v-bind="componentField" />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="endDate" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>End Date</UiFormLabel>
        <UiFormControl>
          <UiInput type="date" v-bind="componentField" />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="progress" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Progress (%)</UiFormLabel>
        <UiFormControl>
          <UiInput type="number" min="0" max="100" v-bind="componentField" />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <UiButton type="submit" class="w-full" :disabled="isSubmitting">
      {{ isSubmitting ? 'Submitting...' : project ? 'Update Project' : 'Create Project' }}
    </UiButton>
  </form>
</template>
