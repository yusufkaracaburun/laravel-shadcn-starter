<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'

import { Button } from '@/components/ui/button'
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useToast } from '@/composables/use-toast.composable'
import { useTimesheets } from '@/pages/timesheets/composables/use-timesheets.composable'
import { setFormFieldErrors } from '@/utils/form'

import type {
  ICreateTimesheetRequest,
  IUpdateTimesheetRequest,
  ITimesheet,
} from '../models/timesheets'

import { createTimesheetFormSchema, editTimesheetFormSchema } from '../data/schema'

const props = defineProps<{
  timesheet?: ITimesheet | null
}>()

const emits = defineEmits<{
  close: []
}>()

const toast = useToast()
const {
  timesheetPrerequisitesResponse,
  createTimesheet,
  updateTimesheet,
  isCreating,
  isUpdating,
  getTimesheetFormInitialValues,
} = useTimesheets()

const isEditMode = computed(() => !!props.timesheet)
const formSchema = computed(() =>
  isEditMode.value ? editTimesheetFormSchema : createTimesheetFormSchema,
)

const initialValues = computed(() => getTimesheetFormInitialValues(props.timesheet))

const form = useForm({
  validationSchema: computed(() => toTypedSchema(formSchema.value)),
  initialValues: initialValues.value,
})

const { handleSubmit, setFieldError, resetForm, values } = form

watch(
  () => props.timesheet,
  (timesheet) => {
    resetForm({ values: initialValues.value })
  },
  { immediate: true },
)

const validFields = [
  'user_id',
  'project_id',
  'task_id',
  'date',
  'hours',
  'description',
  'status',
] as const

const users = computed(() => timesheetPrerequisitesResponse.value?.users || [])
const projects = computed(() => timesheetPrerequisitesResponse.value?.projects || [])
const tasks = computed(() => timesheetPrerequisitesResponse.value?.tasks || [])
const statuses = computed(() => timesheetPrerequisitesResponse.value?.statuses || [])

function prepareRequestData(
  values: any,
  isEdit: boolean,
): IUpdateTimesheetRequest | ICreateTimesheetRequest {
  if (isEdit && props.timesheet) {
    return {
      id: props.timesheet.id,
      ...values,
    }
  }

  return {
    user_id: values.user_id,
    project_id: values.project_id || null,
    task_id: values.task_id || null,
    date: values.date,
    hours: values.hours,
    description: values.description || null,
    status: values.status || null,
  }
}

const onSubmit = handleSubmit(async (values) => {
  try {
    const requestData = prepareRequestData(values, isEditMode.value)

    if (isEditMode.value && props.timesheet) {
      await updateTimesheet(props.timesheet.id, requestData as IUpdateTimesheetRequest)
    } else {
      await createTimesheet(requestData as ICreateTimesheetRequest)
    }

    resetForm()
    emits('close')
  } catch (error: any) {
    setFormFieldErrors(error, setFieldError, validFields)
  }
})
</script>

<template>
  <form class="space-y-4" @submit="onSubmit">
    <FormField v-slot="{ componentField }" name="user_id">
      <FormItem>
        <FormLabel>User</FormLabel>
        <FormControl>
          <Select
            v-bind="componentField"
            :model-value="componentField.modelValue?.toString()"
            @update:model-value="(val) => componentField.onUpdate(val ? Number(val) : null)"
          >
            <SelectTrigger>
              <SelectValue placeholder="Select a user" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="user in users"
                :key="user.id"
                :value="user.id.toString()"
              >
                {{ user.name }} ({{ user.email }})
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="project_id">
      <FormItem>
        <FormLabel>Project</FormLabel>
        <FormControl>
          <Select
            v-bind="componentField"
            :model-value="componentField.modelValue?.toString()"
            @update:model-value="(val) => componentField.onUpdate(val ? Number(val) : null)"
          >
            <SelectTrigger>
              <SelectValue placeholder="Select a project (optional)" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">None</SelectItem>
              <SelectItem
                v-for="project in projects"
                :key="project.id"
                :value="project.id.toString()"
              >
                {{ project.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="task_id">
      <FormItem>
        <FormLabel>Task</FormLabel>
        <FormControl>
          <Select
            v-bind="componentField"
            :model-value="componentField.modelValue?.toString()"
            @update:model-value="(val) => componentField.onUpdate(val ? Number(val) : null)"
          >
            <SelectTrigger>
              <SelectValue placeholder="Select a task (optional)" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">None</SelectItem>
              <SelectItem
                v-for="task in tasks"
                :key="task.id"
                :value="task.id.toString()"
              >
                {{ task.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="date">
      <FormItem>
        <FormLabel>Date</FormLabel>
        <FormControl>
          <Input
            type="date"
            v-bind="componentField"
            placeholder="Date"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="hours">
      <FormItem>
        <FormLabel>Hours</FormLabel>
        <FormControl>
          <Input
            type="number"
            step="0.1"
            min="0.1"
            max="24"
            v-bind="componentField"
            placeholder="8.0"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="description">
      <FormItem>
        <FormLabel>Description</FormLabel>
        <FormControl>
          <Input
            type="text"
            v-bind="componentField"
            placeholder="Work description"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="status">
      <FormItem>
        <FormLabel>Status</FormLabel>
        <FormControl>
          <Select
            v-bind="componentField"
            :model-value="componentField.modelValue?.toString()"
            @update:model-value="(val) => componentField.onUpdate(val || null)"
          >
            <SelectTrigger>
              <SelectValue placeholder="Select a status (optional)" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">None</SelectItem>
              <SelectItem
                v-for="status in statuses"
                :key="status.value"
                :value="status.value"
              >
                {{ status.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <Button
      type="submit"
      class="w-full"
      :disabled="isEditMode ? isUpdating : isCreating"
    >
      <UiSpinner v-if="isEditMode ? isUpdating : isCreating" class="mr-2" />
      {{ isEditMode ? 'Update Timesheet' : 'Create Timesheet' }}
    </Button>
  </form>
</template>
