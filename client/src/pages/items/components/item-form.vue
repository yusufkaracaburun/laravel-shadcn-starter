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
import { Textarea } from '@/components/ui/textarea'
import { useItems } from '@/pages/items/composables/use-items.composable'
import { setFormFieldErrors } from '@/utils/form'

import type {
  ICreateItemRequest,
  IItem,
  IUpdateItemRequest,
} from '../models/items'

import { createItemFormSchema, editItemFormSchema } from '../data/schema'

const props = defineProps<{
  item?: IItem | null
}>()

const emits = defineEmits<{
  close: []
}>()

const {
  createItem,
  updateItem,
  isCreating,
  isUpdating,
  getItemFormInitialValues,
} = useItems()

const isEditMode = computed(() => !!props.item)
const formSchema = computed(() =>
  isEditMode.value ? editItemFormSchema : createItemFormSchema,
)

const initialValues = computed(() => getItemFormInitialValues(props.item))

const form = useForm({
  validationSchema: computed(() => toTypedSchema(formSchema.value)),
  initialValues: initialValues.value,
})

const { handleSubmit, setFieldError, resetForm, values } = form

watch(
  () => props.item,
  (item) => {
    resetForm({ values: initialValues.value })
  },
  { immediate: true },
)

const validFields = [
  'name',
  'description',
  'unit_price',
  'vat_rate',
  'unit',
] as const

function prepareRequestData(
  values: any,
  isEdit: boolean,
): IUpdateItemRequest | ICreateItemRequest {
  if (isEdit && props.item) {
    return {
      ...values,
    }
  }

  return {
    name: values.name,
    description: values.description || null,
    unit_price: values.unit_price,
    vat_rate: values.vat_rate,
    unit: values.unit || null,
  }
}

const onSubmit = handleSubmit(async (values) => {
  try {
    const requestData = prepareRequestData(values, isEditMode.value)

    if (isEditMode.value && props.item) {
      await updateItem(props.item.id, requestData as IUpdateItemRequest)
    } else {
      await createItem(requestData as ICreateItemRequest)
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
          <Input type="text" v-bind="componentField" placeholder="Item name" />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="description">
      <FormItem>
        <FormLabel>Description</FormLabel>
        <FormControl>
          <Textarea
            v-bind="componentField"
            placeholder="Item description"
            :rows="3"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="unit_price">
      <FormItem>
        <FormLabel>Unit Price</FormLabel>
        <FormControl>
          <Input
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            v-bind="componentField"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="vat_rate">
      <FormItem>
        <FormLabel>VAT Rate (%)</FormLabel>
        <FormControl>
          <Input
            type="number"
            step="0.01"
            min="0"
            max="100"
            placeholder="0.00"
            v-bind="componentField"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="unit">
      <FormItem>
        <FormLabel>Unit</FormLabel>
        <FormControl>
          <Input
            type="text"
            placeholder="e.g., pcs, kg, m"
            maxlength="50"
            v-bind="componentField"
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
      {{ isEditMode ? 'Update Item' : 'Create Item' }}
    </Button>
  </form>
</template>
