<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'

import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
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
import { useTeams } from '@/pages/teams/composables/use-teams.composable'
import { setFormFieldErrors } from '@/utils/form'

import type {
  ICreateTeamRequest,
  IUpdateTeamRequest,
  ITeam,
} from '../models/teams'

import { createTeamFormSchema, editTeamFormSchema } from '../data/schema'

const props = defineProps<{
  team?: ITeam | null
}>()

const emits = defineEmits<{
  close: []
}>()

const toast = useToast()
const {
  teamPrerequisitesResponse,
  createTeam,
  updateTeam,
  isCreating,
  isUpdating,
  getTeamFormInitialValues,
} = useTeams()

const isEditMode = computed(() => !!props.team)
const formSchema = computed(() =>
  isEditMode.value ? editTeamFormSchema : createTeamFormSchema,
)

const initialValues = computed(() => getTeamFormInitialValues(props.team))

const form = useForm({
  validationSchema: computed(() => toTypedSchema(formSchema.value)),
  initialValues: initialValues.value,
})

const { handleSubmit, setFieldError, resetForm, values } = form

watch(
  () => props.team,
  (team) => {
    resetForm({ values: initialValues.value })
  },
  { immediate: true },
)

const validFields = [
  'name',
  'personal_team',
  'user_id',
] as const

const users = computed(() => teamPrerequisitesResponse.value?.users || [])

function prepareRequestData(
  values: any,
  isEdit: boolean,
): IUpdateTeamRequest | ICreateTeamRequest {
  if (isEdit && props.team) {
    return {
      id: props.team.id,
      ...values,
    }
  }

  return {
    name: values.name,
    personal_team: values.personal_team || false,
    user_id: values.user_id || null,
  }
}

const onSubmit = handleSubmit(async (values) => {
  try {
    const requestData = prepareRequestData(values, isEditMode.value)

    if (isEditMode.value && props.team) {
      await updateTeam(props.team.id, requestData as IUpdateTeamRequest)
    } else {
      await createTeam(requestData as ICreateTeamRequest)
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
    <FormField v-slot="{ componentField }" name="name">
      <FormItem>
        <FormLabel>Name</FormLabel>
        <FormControl>
          <Input
            type="text"
            v-bind="componentField"
            placeholder="Team Name"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="personal_team">
      <FormItem class="flex flex-row items-start space-x-3 space-y-0">
        <FormControl>
          <Checkbox
            :model-value="componentField.modelValue"
            @update:model-value="componentField.onUpdate"
          />
        </FormControl>
        <div class="space-y-1 leading-none">
          <FormLabel>Personal Team</FormLabel>
          <p class="text-sm text-muted-foreground">
            Mark this as a personal team
          </p>
        </div>
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="user_id">
      <FormItem>
        <FormLabel>Owner</FormLabel>
        <FormControl>
          <Select
            v-bind="componentField"
            :model-value="componentField.modelValue?.toString()"
            @update:model-value="(val) => componentField.onUpdate(val ? Number(val) : null)"
          >
            <SelectTrigger>
              <SelectValue placeholder="Select an owner" />
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

    <Button
      type="submit"
      class="w-full"
      :disabled="isEditMode ? isUpdating : isCreating"
    >
      <UiSpinner v-if="isEditMode ? isUpdating : isCreating" class="mr-2" />
      {{ isEditMode ? 'Update Team' : 'Create Team' }}
    </Button>
  </form>
</template>
