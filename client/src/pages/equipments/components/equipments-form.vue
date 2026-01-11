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
import { useToast } from '@/composables/use-toast.composable'
import { useEquipments } from '@/pages/equipments/composables/use-equipments.composable'
import { setFormFieldErrors } from '@/utils/form'

import type {
  ICreateEquipmentRequest,
  IUpdateEquipmentRequest,
  IEquipment,
} from '../models/equipments'

import { createEquipmentsFormSchema, editEquipmentsFormSchema } from '../data/schema'

const props = defineProps<{
  equipment?: IEquipment | null
}>()

const emits = defineEmits<{
  close: []
}>()

const toast = useToast()
const {
  equipmentsPrerequisitesResponse,
  createEquipments,
  updateEquipments,
  isCreating,
  isUpdating,
  getEquipmentsFormInitialValues,
} = useEquipments()

const isEditMode = computed(() => !!props.equipment)
const formSchema = computed(() =>
  isEditMode.value ? editEquipmentsFormSchema : createEquipmentsFormSchema,
)

const initialValues = computed(() => getEquipmentsFormInitialValues(props.equipment))

const form = useForm({
  validationSchema: computed(() => toTypedSchema(formSchema.value)),
  initialValues: initialValues.value,
})

const { handleSubmit, setFieldError, resetForm, values } = form

watch(
  () => props.equipment,
  (equipment) => {
    resetForm({ values: initialValues.value })
  },
  { immediate: true },
)

const validFields = [
  'name',
  'type',
  'model',
  'serial_number',
  'status',
] as const

function prepareRequestData(
  values: any,
  isEdit: boolean,
): IUpdateEquipmentRequest | ICreateEquipmentRequest {
  if (isEdit && props.equipment) {
    return {
      id: props.equipment.id,
      ...values,
    }
  }

  return {
    name: values.name,
    type: values.type || null,
    model: values.model || null,
    serial_number: values.serial_number || null,
    status: values.status || null,
  }
}

const onSubmit = handleSubmit(async (values) => {
  try {
    const requestData = prepareRequestData(values, isEditMode.value)

    if (isEditMode.value && props.equipment) {
      await updateEquipments(props.equipment.id, requestData as IUpdateEquipmentRequest)
    } else {
      await createEquipments(requestData as ICreateEquipmentRequest)
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
            placeholder="Equipment Name"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="type">
      <FormItem>
        <FormLabel>Type</FormLabel>
        <FormControl>
          <Input
            type="text"
            v-bind="componentField"
            placeholder="Equipment Type"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="model">
      <FormItem>
        <FormLabel>Model</FormLabel>
        <FormControl>
          <Input
            type="text"
            v-bind="componentField"
            placeholder="Model"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="serial_number">
      <FormItem>
        <FormLabel>Serial Number</FormLabel>
        <FormControl>
          <Input
            type="text"
            v-bind="componentField"
            placeholder="SN-123456"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="status">
      <FormItem>
        <FormLabel>Status</FormLabel>
        <FormControl>
          <Input
            type="text"
            v-bind="componentField"
            placeholder="Active"
          />
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
      {{ isEditMode ? 'Update Equipment' : 'Create Equipment' }}
    </Button>
  </form>
</template>
