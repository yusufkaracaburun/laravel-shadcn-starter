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
import { useVehicles } from '@/pages/vehicles/composables/use-vehicles.composable'
import { setFormFieldErrors } from '@/utils/form'

import type {
  ICreateVehicleRequest,
  IUpdateVehicleRequest,
  IVehicle,
} from '../models/vehicles'

import { createVehicleFormSchema, editVehicleFormSchema } from '../data/schema'

const props = defineProps<{
  vehicle?: IVehicle | null
}>()

const emits = defineEmits<{
  close: []
}>()

const toast = useToast()
const {
  vehiclePrerequisitesResponse,
  createVehicle,
  updateVehicle,
  isCreating,
  isUpdating,
  getVehicleFormInitialValues,
} = useVehicles()

const isEditMode = computed(() => !!props.vehicle)
const formSchema = computed(() =>
  isEditMode.value ? editVehicleFormSchema : createVehicleFormSchema,
)

const initialValues = computed(() => getVehicleFormInitialValues(props.vehicle))

const form = useForm({
  validationSchema: computed(() => toTypedSchema(formSchema.value)),
  initialValues: initialValues.value,
})

const { handleSubmit, setFieldError, resetForm, values } = form

watch(
  () => props.vehicle,
  (vehicle) => {
    resetForm({ values: initialValues.value })
  },
  { immediate: true },
)

const validFields = [
  'license_plate',
  'make',
  'model',
  'year',
  'color',
  'vin',
] as const

function prepareRequestData(
  values: any,
  isEdit: boolean,
): IUpdateVehicleRequest | ICreateVehicleRequest {
  if (isEdit && props.vehicle) {
    return {
      id: props.vehicle.id,
      ...values,
    }
  }

  return {
    license_plate: values.license_plate,
    make: values.make || null,
    model: values.model || null,
    year: values.year || null,
    color: values.color || null,
    vin: values.vin || null,
  }
}

const onSubmit = handleSubmit(async (values) => {
  try {
    const requestData = prepareRequestData(values, isEditMode.value)

    if (isEditMode.value && props.vehicle) {
      await updateVehicle(props.vehicle.id, requestData as IUpdateVehicleRequest)
    } else {
      await createVehicle(requestData as ICreateVehicleRequest)
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
    <FormField v-slot="{ componentField }" name="license_plate">
      <FormItem>
        <FormLabel>License Plate</FormLabel>
        <FormControl>
          <Input
            type="text"
            v-bind="componentField"
            placeholder="ABC-1234"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="make">
      <FormItem>
        <FormLabel>Make</FormLabel>
        <FormControl>
          <Input
            type="text"
            v-bind="componentField"
            placeholder="Toyota"
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
            placeholder="Camry"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="year">
      <FormItem>
        <FormLabel>Year</FormLabel>
        <FormControl>
          <Input
            type="number"
            v-bind="componentField"
            placeholder="2020"
            :min="1900"
            :max="new Date().getFullYear() + 1"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="color">
      <FormItem>
        <FormLabel>Color</FormLabel>
        <FormControl>
          <Input
            type="text"
            v-bind="componentField"
            placeholder="Red"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="vin">
      <FormItem>
        <FormLabel>VIN</FormLabel>
        <FormControl>
          <Input
            type="text"
            v-bind="componentField"
            placeholder="1HGBH41JXMN109186"
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
      {{ isEditMode ? 'Update Vehicle' : 'Create Vehicle' }}
    </Button>
  </form>
</template>
