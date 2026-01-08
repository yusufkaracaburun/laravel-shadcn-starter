<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'

import { FormField } from '@/components/ui/form'
import { useItems } from '@/composables/use-items'

import type { Item } from '../data/schema'

const props = defineProps<{
  item: Item | null
}>()
const emits = defineEmits(['close'])

const { createItem, updateItem } = useItems()

// Extract unit_price value - handle both Money object and number
function getUnitPriceValue(item: Item | null): number {
  if (!item?.unit_price)
    return 0
  // If it's a Money object, extract the decimal value
  if (typeof item.unit_price === 'object' && 'amount' in item.unit_price) {
    // Money object: amount is in cents, convert to decimal
    const amount = Number.parseFloat(item.unit_price.amount)
    return amount / 100
  }
  // If it's already a number, use it directly
  if (typeof item.unit_price === 'number') {
    return item.unit_price
  }
  return 0
}

const formSchema = toTypedSchema(
  z.object({
    name: z
      .string()
      .min(2, 'Name must be at least 2 characters')
      .max(255, 'Name must not exceed 255 characters')
      .default(props.item?.name ?? ''),
    description: z
      .string()
      .nullable()
      .default(props.item?.description ?? null),
    unit_price: z
      .number()
      .min(0, 'Unit price must be zero or greater')
      .default(getUnitPriceValue(props.item)),
    vat_rate: z
      .number()
      .min(0, 'VAT rate must be zero or greater')
      .max(100, 'VAT rate may not be greater than 100%')
      .default(props.item?.vat_rate ?? 0),
    unit: z
      .string()
      .max(50, 'Unit must not exceed 50 characters')
      .nullable()
      .default(props.item?.unit ?? null),
  }),
)

// Compute initial values reactively
const initialValues = computed(() => ({
  name: props.item?.name ?? '',
  description: props.item?.description ?? null,
  unit_price: getUnitPriceValue(props.item),
  vat_rate: props.item?.vat_rate ?? 0,
  unit: props.item?.unit ?? null,
}))

const { isFieldDirty, handleSubmit, isSubmitting, resetForm } = useForm({
  validationSchema: formSchema,
  initialValues: initialValues.value,
})

// Reset form when item changes
watch(
  () => props.item,
  (newItem) => {
    if (newItem) {
      resetForm({
        values: {
          name: newItem.name ?? '',
          description: newItem.description ?? null,
          unit_price: getUnitPriceValue(newItem),
          vat_rate: newItem.vat_rate ?? 0,
          unit: newItem.unit ?? null,
        },
      })
    }
  },
  { deep: true },
)

const onSubmit = handleSubmit(async (values) => {
  try {
    const backendData = {
      name: values.name,
      description: values.description || null,
      unit_price: values.unit_price,
      vat_rate: values.vat_rate,
      unit: values.unit || null,
    }

    if (props.item?.id) {
      // Update existing item
      await updateItem(props.item.id, backendData)
    } else {
      // Create new item
      await createItem(backendData)
    }

    emits('close')
  } catch (error) {
    // Error handling is done in the composable
    // Just log for debugging
    console.error('Item form submission error:', error)
  }
})
</script>

<template>
  <form class="space-y-4" @submit="onSubmit">
    <FormField
      v-slot="{ componentField }"
      name="name"
      :validate-on-blur="!isFieldDirty"
    >
      <UiFormItem>
        <UiFormLabel>Name</UiFormLabel>
        <UiFormControl>
          <UiInput
            type="text"
            placeholder="Item name"
            v-bind="componentField"
          />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField
      v-slot="{ componentField }"
      name="description"
      :validate-on-blur="!isFieldDirty"
    >
      <UiFormItem>
        <UiFormLabel>Description</UiFormLabel>
        <UiFormControl>
          <UiTextarea placeholder="Item description" v-bind="componentField" />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField
      v-slot="{ componentField }"
      name="unit_price"
      :validate-on-blur="!isFieldDirty"
    >
      <UiFormItem>
        <UiFormLabel>Unit Price</UiFormLabel>
        <UiFormControl>
          <UiInput
            type="number"
            step="0.01"
            min="0"
            placeholder="0.00"
            v-bind="componentField"
          />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField
      v-slot="{ componentField }"
      name="vat_rate"
      :validate-on-blur="!isFieldDirty"
    >
      <UiFormItem>
        <UiFormLabel>VAT Rate (%)</UiFormLabel>
        <UiFormControl>
          <UiInput
            type="number"
            step="0.01"
            min="0"
            max="100"
            placeholder="0.00"
            v-bind="componentField"
          />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField
      v-slot="{ componentField }"
      name="unit"
      :validate-on-blur="!isFieldDirty"
    >
      <UiFormItem>
        <UiFormLabel>Unit</UiFormLabel>
        <UiFormControl>
          <UiInput
            type="text"
            placeholder="e.g., pcs, kg, m"
            maxlength="50"
            v-bind="componentField"
          />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <UiButton type="submit" class="w-full" :disabled="isSubmitting">
      {{
        isSubmitting ? 'Submitting...' : item ? 'Update Item' : 'Create Item'
      }}
    </UiButton>
  </form>
</template>
