<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { computed, watch } from 'vue'
import { z } from 'zod'

import { Button } from '@/components/ui/button'
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import {
  InputGroup,
  InputGroupAddon,
  InputGroupInput,
  InputGroupText,
} from '@/components/ui/input-group'
import { Label } from '@/components/ui/label'
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group'

import type { TInvoiceItem } from '../data/schema'

import { calculateItemTotals } from '../utils/calculations'
import { formatMoney } from '../utils/formatters'

const props = defineProps<{
  item: TInvoiceItem | null
  invoiceId?: number
}>()

const emits = defineEmits(['save', 'cancel'])

const formSchema = toTypedSchema(
  z.object({
    description: z.string().nullable().optional(),
    quantity: z
      .number()
      .min(0.01, 'Quantity must be greater than zero')
      .refine((val) => {
        const decimalPlaces = (val.toString().split('.')[1] || '').length
        return decimalPlaces <= 2
      }, 'Quantity must have a maximum of 2 decimal places'),
    unit_price: z.number().min(0, 'Unit price must be zero or greater'),
    unit: z.string().nullable().optional(),
    vat_rate: z.enum(['0', '9', '21']).default('21'),
  }),
)

// Extract unit_price value - handle both Money object and number
function getUnitPriceValue(item: TInvoiceItem | null): number {
  if (!item?.unit_price)
    return 0
  if (typeof item.unit_price === 'object' && 'amount' in item.unit_price) {
    const amount = Number.parseFloat(item.unit_price.amount)
    return amount / 100
  }
  if (typeof item.unit_price === 'number') {
    return item.unit_price
  }
  return 0
}

function getInitialValues() {
  const vatRate = props.item?.vat_rate ?? 21
  // Convert number to string for enum validation, then transform back to number
  // Default to '21' if vatRate is not 0, 9, or 21
  const vatRateStr: '0' | '9' | '21' = vatRate === 0 ? '0' : vatRate === 9 ? '9' : '21'
  return {
    description: props.item?.description ?? null,
    quantity: props.item?.quantity ?? 1,
    unit_price: getUnitPriceValue(props.item),
    unit: (props.item as any)?.unit ?? null,
    vat_rate: vatRateStr, // Defaults to '21' when no item or vat_rate is not 0 or 9
  }
}

// Make initial values reactive
const initialValues = computed(() => getInitialValues())

const { values, isFieldDirty, handleSubmit, isSubmitting, resetForm, setFieldValue } = useForm({
  validationSchema: formSchema,
  initialValues: initialValues.value,
})

// Calculate totals in real-time
const calculatedTotals = computed(() => {
  const vatRate = Number.parseInt(values.vat_rate || '21')
  return calculateItemTotals(values.quantity || 0, values.unit_price || 0, vatRate)
})

// Reset form when item changes
watch(
  () => props.item,
  () => {
    const newValues = getInitialValues()
    resetForm({
      values: newValues,
    })
    // Explicitly set the vat_rate field to ensure ToggleGroup receives the correct value
    setFieldValue('vat_rate', newValues.vat_rate)
  },
  { deep: true, immediate: true },
)

const onSubmit = handleSubmit(async (formValues) => {
  emits('save', {
    description: formValues.description || null,
    quantity: formValues.quantity,
    unit_price: formValues.unit_price,
    unit: formValues.unit || null,
    vat_rate: Number.parseInt(formValues.vat_rate), // Convert string to number for submission
  })
})

function onCancel() {
  emits('cancel')
}
</script>

<template>
  <form class="space-y-4" @submit.prevent="onSubmit">
    <!-- Name - Full Width -->
    <FormField v-slot="{ componentField }" name="description" :validate-on-blur="!isFieldDirty">
      <FormItem>
        <FormLabel>Name</FormLabel>
        <FormControl>
          <Input type="text" placeholder="Item name" v-bind="componentField" />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <!-- Quantity & Unit, Unit Price & VAT Rate - One Row -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-start">
      <!-- Quantity and Unit - Input Group -->
      <FormField
        v-slot="{ componentField: quantityField }"
        name="quantity"
        :validate-on-blur="!isFieldDirty"
      >
        <FormItem>
          <FormLabel>Quantity & Unit</FormLabel>
          <FormControl>
            <InputGroup>
              <InputGroupInput
                type="number"
                step="0.01"
                min="0.01"
                placeholder="1,00"
                v-bind="quantityField"
              />
              <FormField
                v-slot="{ componentField: unitField }"
                name="unit"
                :validate-on-blur="!isFieldDirty"
              >
                <InputGroupAddon align="inline-end">
                  <InputGroupInput
                    type="text"
                    placeholder="pcs"
                    v-bind="unitField"
                    class="w-20 text-center"
                  />
                </InputGroupAddon>
              </FormField>
            </InputGroup>
          </FormControl>
          <div class="space-y-1">
            <FormField name="quantity" :validate-on-blur="!isFieldDirty">
              <FormMessage />
            </FormField>
            <FormField name="unit" :validate-on-blur="!isFieldDirty">
              <FormMessage />
            </FormField>
          </div>
        </FormItem>
      </FormField>

      <!-- Unit Price and VAT Rate - Input Group -->
      <FormField
        v-slot="{ componentField: priceField }"
        name="unit_price"
        :validate-on-blur="!isFieldDirty"
      >
        <FormItem>
          <FormLabel>Unit Price & VAT Rate</FormLabel>
          <FormControl>
            <InputGroup>
              <InputGroupAddon>
                <InputGroupText>€</InputGroupText>
              </InputGroupAddon>
              <InputGroupInput
                type="number"
                step="0.01"
                min="0"
                placeholder="0,00"
                v-bind="priceField"
              />
              <FormField
                v-slot="{ componentField: vatField }"
                name="vat_rate"
                :validate-on-blur="!isFieldDirty"
              >
                <InputGroupAddon align="inline-end" class="!p-0">
                  <ToggleGroup
                    type="single"
                    variant="outline"
                    class="flex h-full"
                    v-bind="vatField"
                  >
                    <ToggleGroupItem
                      value="0"
                      class="flex-1 rounded-none first:rounded-r-md"
                      aria-label="VAT 0%"
                    >
                      <span class="font-semibold text-xs">0%</span>
                    </ToggleGroupItem>
                    <ToggleGroupItem value="9" class="flex-1 rounded-none" aria-label="VAT 9%">
                      <span class="font-semibold text-xs">9%</span>
                    </ToggleGroupItem>
                    <ToggleGroupItem
                      value="21"
                      class="flex-1 rounded-none last:rounded-r-md"
                      aria-label="VAT 21%"
                    >
                      <span class="font-semibold text-xs">21%</span>
                    </ToggleGroupItem>
                  </ToggleGroup>
                </InputGroupAddon>
              </FormField>
            </InputGroup>
          </FormControl>
          <div class="space-y-1">
            <FormField name="unit_price" :validate-on-blur="!isFieldDirty">
              <FormMessage />
            </FormField>
            <FormField name="vat_rate" :validate-on-blur="!isFieldDirty">
              <FormMessage />
            </FormField>
          </div>
        </FormItem>
      </FormField>
    </div>

    <!-- Calculated Totals Preview -->
    <div class="rounded-lg border bg-muted/50 p-4">
      <div class="grid grid-cols-3 gap-4 text-sm">
        <div>
          <p class="text-muted-foreground">
            Excl. VAT
          </p>
          <p class="font-semibold">
            {{ formatMoney(calculatedTotals.totalExclVat) }}
          </p>
        </div>
        <div>
          <p class="text-muted-foreground">
            VAT
          </p>
          <p class="font-semibold">
            {{ formatMoney(calculatedTotals.totalVat) }}
          </p>
        </div>
        <div>
          <p class="text-muted-foreground">
            Incl. VAT
          </p>
          <p class="font-semibold">
            {{ formatMoney(calculatedTotals.totalInclVat) }}
          </p>
        </div>
      </div>
    </div>

    <div class="flex justify-end gap-2">
      <Button type="button" variant="outline" @click="onCancel">
        Cancel
      </Button>
      <Button type="submit" :disabled="isSubmitting">
        {{ item ? 'Update' : 'Add' }} Item
      </Button>
    </div>
  </form>
</template>
